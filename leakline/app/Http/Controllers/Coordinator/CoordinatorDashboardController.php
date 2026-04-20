<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\IncidentEvent;
use Illuminate\Http\Request;
use App\Models\Incident;
use App\Models\IncidentRelation;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WorkOrder;

class CoordinatorDashboardController extends Controller
{
    public function index(Request $request)
    {

        $sort = $request->query('sort','age');
        $status = $request->query('sort_status');
        $q= Incident::query()->with(['severity', 'slaRule']);

        if ($status) {
            $q->where('status', $status);
        }
        $statuses = Incident::query()
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');


        if ($sort === 'sla_risk') {
            $q->leftJoin('sla_rules', 'sla_rules.severity_id', '=', 'incidents.severity_id')
                ->select('incidents.*')
                ->orderByRaw("(incidents.created_at + (sla_rules.resolution_time || ' hours')::interval) ASC NULLS LAST");
        } elseif ($sort === 'severity') {
            // simple severity ordering (by severity_id).
            $q->orderByDesc('incidents.severity_id');
        } else {
            // age (newest first)
            $q->orderByDesc('incidents.created_at');
        }

        $incidents = $q->paginate(10)->withQueryString();



        $incidentMarkers = Incident::query()
            ->selectRaw("
                id, status, severity_id,
                ST_Y(location_geom::geometry) AS lat,
                ST_X(location_geom::geometry) AS lng
            ")
            ->whereNotNull('location_geom')
            ->latest()
            ->take(500)
            ->get()
            ->values()
            ->toArray();

        return view('coordinator.dashboard', compact('incidents', 'sort', 'incidentMarkers','statuses','status'));
    }



    public function show(Incident $incident)
    {
        $incident->load(['severity', 'slaRule', 'category', 'area', 'contact', 'media', 'events.actor']);

        $currentWorkOrder = WorkOrder::query()
            ->where('incident_id', $incident->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['technician'])
            ->latest('id')
            ->first();

        $technicians = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'technician'))
            ->withCount([
                'workOrders as active_jobs_count' => fn ($query) => $query->whereIn('status', ['assigned', 'in_progress', 'on_hold']),
                'workOrders as inbox_jobs_count' => fn ($query) => $query->where('status', 'assigned'),
                'workOrders as in_progress_jobs_count' => fn ($query) => $query->whereIn('status', ['in_progress', 'on_hold']),
                'workOrders as completed_jobs_count' => fn ($query) => $query->whereIn('status', ['done', 'cancelled']),
            ])
            ->orderBy('active_jobs_count')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $technicians->transform(function (User $technician) {
            $technician->availability_label = match (true) {
                $technician->active_jobs_count === 0 => 'Available',
                $technician->active_jobs_count <= 2 => 'Busy',
                default => 'Busy',
            };

            $technician->availability_badge = match (true) {
                $technician->active_jobs_count === 0 => 'bg-green-100 text-green-700',
                $technician->active_jobs_count <= 2 => 'bg-amber-100 text-amber-700',
                default => 'bg-amber-100 text-amber-700',
            };

            return $technician;
        });


        $duplicates = collect(); // save in an empty collection

        if ($incident->latitude !== null && $incident->longitude !== null) {
            $duplicates = Incident::query()
                ->whereKeyNot($incident->id)
                ->whereNotNull('location_geom')
                ->where('created_at', '>=', now()->subHours(24))
                ->whereNotIn('status', ['closed', 'resolved', 'cancelled'])
                ->whereRaw(
                    "ST_DWithin(incidents.location_geom::geography, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)",
                    [$incident->longitude, $incident->latitude, 75]
                )
                ->latest()
                ->limit(10)
                ->get();
        }
        return view('coordinator.incidents.show', compact('incident', 'duplicates', 'technicians', 'currentWorkOrder'));
    }

    // Assign directly to a technician
    public function assignTechnician(Request $request, Incident $incident)
    {
        $data = $request->validate([
            'technician_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $technician = User::query()->with('role')->findOrFail($data['technician_id']);

        if ($technician->role?->name !== 'technician') {
            return back()->withErrors(['technician_id' => 'Selected user is not a technician.']);
        }

        $workOrder = WorkOrder::query()
            ->where('incident_id', $incident->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->latest('id')
            ->first();

        if ($workOrder && in_array($workOrder->status, ['assigned', 'in_progress', 'on_hold'], true) && (int) $workOrder->assigned_to === (int) $technician->id) {
            return back()->withErrors([
                'technician_id' => 'This technician is already assigned to this incident. Ask them to reject it first or choose another technician.',
            ]);
        }

        if (!$workOrder) {
            $workOrder = WorkOrder::create([
                'incident_id' => $incident->id,
                'assigned_to' => $technician->id,
                'status' => 'assigned',
                'field_status' => null,
            ]);
        } else {
            $workOrder->update([
                'assigned_to' => $technician->id,
                'status' => 'assigned',
                'field_status' => null,
            ]);
        }

        IncidentEvent::create([
            'incident_id' => $incident->id,
            'actor_id' => auth()->id(),
            'event_type' => 'assigned',
            'message' => "Assigned to technician: {$technician->name}.",
            'meta' => [
                'work_order_id' => $workOrder->id,
                'technician_id' => $technician->id,
                'technician_name' => $technician->name,
            ],
            'created_at' => now(),
        ]);

        return back()->with('status', 'Incident assigned to technician successfully.');
    }


    public function merge(Request $request, Incident $incident){
        $data = $request->validate([
            'duplicate_id' => ['required', 'integer', 'exists:incidents,id'],
        ]);

        // Don't allow self-merge

        if ((int) $data['duplicate_id'] === (int) $incident->id) {
            return back()->with('status', 'You cannot merge an incident into itself.');
        }

        $duplicate = Incident::findOrFail($data['duplicate_id']);

        DB::transaction(function () use ($incident, $duplicate) {
            // Save relation (duplicate -> main incident)
            IncidentRelation::firstOrCreate(
                [
                    'source_incident_id' => $duplicate->id,
                    'target_incident_id' => $incident->id,
                    'relation' => 'duplicate_of',
                ],
                [
                    'merged_by' => auth()->id(),
                    'merged_at' => now(),
                    'note' => 'Merged from coordinator incident page',
                ]
            );

            // Mark duplicate as cancelled so queue stays clean
            if (!in_array($duplicate->status, ['closed', 'resolved', 'cancelled'], true)) {
                $duplicate->update([
                    'status' => 'cancelled',
                    'closed_at' => now(),
                ]);
            }

            // timeline entries
            IncidentEvent::create([
                'incident_id' => $incident->id,
                'actor_id' => auth()->id(),
                'event_type' => 'merged',
                'message' => "Merged duplicate incident #{$duplicate->id} into this incident.",
                'meta' => ['duplicate_id' => $duplicate->id],
                'created_at' => now(),
            ]);

            IncidentEvent::create([
                'incident_id' => $duplicate->id,
                'actor_id' => auth()->id(),
                'event_type' => 'merged',
                'message' => "Marked as duplicate of incident #{$incident->id}.",
                'meta' => ['target_incident_id' => $incident->id],
                'created_at' => now(),
            ]);
        });

        return back()->with('status', 'Duplicate merged successfully.');
    }
}
