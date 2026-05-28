<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\LaborLog;
use App\Models\Material;
use App\Models\ResolutionCode;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use App\Models\IncidentEvent;
use App\Models\Notification;
use App\Models\User;
use App\Models\IncidentMedia;
use Illuminate\Support\Facades\DB;
class TechnicianController extends Controller
{
    // Show job inbox
    public function index()
    {
        $userId = auth()->id();

        $workOrders = WorkOrder::query()
            ->where('assigned_to', $userId)
            ->with(['incident', 'incident.severity', 'technician'])
            ->orderByRaw("CASE WHEN status = 'assigned' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->get();

        $inboxWorkOrders = $workOrders->where('status', 'assigned')->values();
        $activeWorkOrders = $workOrders->whereIn('status', ['in_progress', 'on_hold'])->values();
        $completedWorkOrders = $workOrders->whereIn('status', ['done', 'cancelled'])->values();

        return view('technician.dashboard', compact('inboxWorkOrders', 'activeWorkOrders', 'completedWorkOrders'));
    }

    // Accept assigned job (Inbox -> Active)
    public function accept(WorkOrder $workOrder)
    {
        $this->authorizeAssignedWorkOrder($workOrder);

        if ($workOrder->status !== 'assigned') {
            return back()->withErrors(['accept' => 'This job cannot be accepted because it is not in the assigned state.']);
        }

        $workOrder->update([
            'assigned_to' => auth()->id(),
            'status' => 'in_progress',
        ]);

        IncidentEvent::create([
            'incident_id' => $workOrder->incident_id,
            'actor_id' => auth()->id(),
            'event_type' => 'assigned',
            'message' => 'Job accepted by technician: '.auth()->user()->name,
            'meta' => ['work_order_id' => $workOrder->id],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Job accepted.');
    }

    // Decline job (return to queue)
    public function decline(Request $request, WorkOrder $workOrder)
    {
        $this->authorizeAssignedWorkOrder($workOrder);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $technicianName = auth()->user()->name;
        $reason = $validated['reason'];

        IncidentEvent::create([
            'incident_id' => $workOrder->incident_id,
            'actor_id' => auth()->id(),
            'event_type' => 'comment',
            'message' => "Technician '{$technicianName}' rejected assignment. Reason: {$reason}",
            'meta' => [
                'work_order_id' => $workOrder->id,
                'rejection_reason' => $reason,
            ],
            'created_at' => now(),
        ]);



        $workOrder->update([
            'assigned_to' => null,
            'status' => 'queued',
            'field_status' => null,
        ]);

        return redirect()->route('technician.dashboard')
            ->with('success', 'Job rejected.');
    }


    // Update field movement status for active jobs
    public function updateFieldStatus(Request $request, WorkOrder $workOrder)
    {
        $this->authorizeAssignedWorkOrder($workOrder);

        $validated = $request->validate([
            'field_status' => ['required', 'in:on_route,on_site'],
        ]);

        if (!in_array($workOrder->status, ['in_progress', 'on_hold'], true)) {
            return back()->withErrors(['field_status' => 'Accept the job first before setting field status.']);
        }

        $workOrder->update([
            'field_status' => $validated['field_status'],
        ]);

        IncidentEvent::create([
            'incident_id' => $workOrder->incident_id,
            'actor_id' => auth()->id(),
            'event_type' => 'status_changed',
            'message' => 'Field status updated to '.str_replace('_', ' ', $validated['field_status']).' by technician: '.auth()->user()->name,
            'meta' => [
                'work_order_id' => $workOrder->id,
                'field_status' => $validated['field_status'],
            ],
            'created_at' => now(),
        ]);

        return back()->with('success', 'Field status updated.');
    }

    // Show single work order details
    public function show(WorkOrder $workOrder)
    {
        $this->authorizeAssignedWorkOrder($workOrder);

        $workOrder->load([
            'incident',
            'checklists',
            'materials',
            'laborLogs',
            'incident.severity',
            'incident.category',
            'incident.area',
            'incident.contact',
            'incident.media',
            'incident.events.actor',
            'resolutionCode',
        ]);

        $resolutionCodes = ResolutionCode::all();

        return view('technician.show', compact('workOrder', 'resolutionCodes'));
    }

    // Closing ticket
    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $this->authorizeAssignedWorkOrder($workOrder);

        if (in_array($workOrder->status, ['done', 'cancelled'], true)) {
            abort(403, 'Cannot update a completed or cancelled job');
        }


        if (!in_array($workOrder->status, ['in_progress', 'on_hold'], true)) {
            return back()->withErrors(['status' => 'Accept the job first before closing it.']);
        }

        $validated = $request->validate([
            'resolution_code_id' => ['required', 'exists:resolution_codes,id'],
            'media' => ['nullable', 'array'],
            'media.*' => ['file', 'max:20480'],
            'materials_new' => ['nullable', 'array'],
            'materials_new.*.item_name' => ['nullable', 'string', 'max:255'],
            'materials_new.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'materials_new.*.unit' => ['nullable', 'string', 'max:50'],
            'materials_new.*.cost' => ['nullable', 'numeric', 'min:0'],
            'labor_log' => ['nullable', 'array'],
            'labor_log.started_at' => ['nullable', 'required_with:labor_log.ended_at', 'date'],
            'labor_log.ended_at' => ['nullable', 'required_with:labor_log.started_at', 'date'],
            'labor_log.hours' => ['nullable', 'numeric', 'min:0'],
            'labor_log.notes' => ['nullable', 'string', 'max:2000'],
            ],
            [
                'resolution_code_id.required' => 'Please select a resolution code.',
                'resolution_code_id.exists' => 'The selected resolution code is invalid.',

                'media.*.file' => 'Each media item must be a file.',
                'media.*.max' => 'Each media file must be 20MB or less.',

                'materials_new.*.item_name.string' => 'Material name must be text.',
                'materials_new.*.quantity.numeric' => 'Quantity must be a number.',
                'materials_new.*.quantity.min' => 'Quantity cannot be negative.',
                'materials_new.*.cost.numeric' => 'Cost must be a number.',
                'materials_new.*.cost.min' => 'Cost cannot be negative.',

                'labor_log.started_at.required_with' => 'Please enter the time work started.',
                'labor_log.started_at.date' => 'Start time must be a valid date.',
                'labor_log.ended_at.required_with' => 'Please enter the time work ended.',
                'labor_log.ended_at.date' => 'End time must be a valid date.',
                'labor_log.hours.numeric' => 'Hours must be a number.',
                'labor_log.hours.min' => 'Hours cannot be negative.',
                'labor_log.notes.string' => 'Notes must be text.',
                'labor_log.notes.max' => 'Notes may not be greater than 2000 characters.',
            ]
        );


        $laborLogData = $validated['labor_log'] ?? null;
        if (
            is_array($laborLogData)
            && !empty($laborLogData['started_at'])
            && !empty($laborLogData['ended_at'])
            && strtotime((string) $laborLogData['ended_at']) < strtotime((string) $laborLogData['started_at'])
        ) {
            return back()->withErrors(['labor_log' => 'Labor log ended time must be after started time.']);
        }

        DB::transaction(function () use ($request, $workOrder, $validated): void {
            // Close ticket with required resolution code
            $workOrder->update([
                'status' => 'done',
                'resolution_code_id' => $validated['resolution_code_id'],
            ]);

            if ($workOrder->incident && $workOrder->incident->closed_at === null) {
                $workOrder->incident->update([
                    'closed_at' => now(),
                ]);
            }
            // Track uploaded media event to show it in timeline
            $uploadedMediaCount = 0;
            $uploadedMediaTypes = [];

            // Upload photos/videos to incident media
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $path = $file->store('incident_media', 'public');
                    $mime = $file->getMimeType();
                    $type = str_starts_with((string) $mime, 'video/') ? 'video' : 'image';

                    IncidentMedia::create([
                        'incident_id' => $workOrder->incident_id,
                        'file_url' => $path,
                        'media_type' => $type,
                    ]);
                    $uploadedMediaCount++;
                    $uploadedMediaTypes[] = $type;
                }
            }
            // Timeline event when media is uploaded
            if($uploadedMediaCount > 0) {
                IncidentEvent::create([
                    'incident_id' => $workOrder->incident_id,
                    'actor_id' => auth()->id(),
                    'event_type' => 'media_added',
                    'message' => "Technician added {$uploadedMediaCount} media file(s): " . auth()->user()->name,
                    'meta' => [
                        'work_order_id' => $workOrder->id,
                        'count' => $uploadedMediaCount,
                        'types' => array_values(array_unique($uploadedMediaTypes)),
                    ],
                    'created_at' => now(),
                ]);
            }

            $materialsData = $validated['materials_new'] ?? [];
            if (!empty($materialsData)) {
                foreach ($materialsData as $materialData) {
                    $isEmptyRow = empty(trim((string) ($materialData['item_name'] ?? '')))
                        && ($materialData['quantity'] === null || $materialData['quantity'] === '')
                        && empty(trim((string) ($materialData['unit'] ?? '')))
                        && ($materialData['cost'] === null || $materialData['cost'] === '');

                    if ($isEmptyRow) {
                        continue;
                    }

                    Material::create([
                        'workorder_id' => $workOrder->id,
                        'item_name' => $materialData['item_name'],
                        'quantity' => $materialData['quantity'] ?? 0,
                        'unit' => $materialData['unit'] ?? null,
                        'cost' => $materialData['cost'] ?? 0,
                    ]);
                }
            }

            $laborLogData = $validated['labor_log'] ?? null;
            if (is_array($laborLogData)) {
                $isEmptyRow = empty((string) ($laborLogData['started_at'] ?? ''))
                    && empty((string) ($laborLogData['ended_at'] ?? ''))
                    && (($laborLogData['hours'] ?? null) === null || ($laborLogData['hours'] ?? '') === '')
                    && empty(trim((string) ($laborLogData['notes'] ?? '')));

                if (!$isEmptyRow) {
                    LaborLog::create([
                        'workorder_id' => $workOrder->id,
                        'user_id' => auth()->id(),
                        'started_at' => $laborLogData['started_at'] ?? null,
                        'ended_at' => $laborLogData['ended_at'] ?? null,
                        'hours' => $laborLogData['hours'] ?? null,
                        'notes' => $laborLogData['notes'] ?? null,
                    ]);
                }
            }

            IncidentEvent::create([
                'incident_id' => $workOrder->incident_id,
                'actor_id' => auth()->id(),
                'event_type' => 'status_changed',
                'message' => 'Job closed by technician: ' . auth()->user()->name,
                'meta' => [
                    'work_order_id' => $workOrder->id,
                    'status' => 'done',
                    'resolution_code_id' => $validated['resolution_code_id'],
                ],
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'Ticket closed successfully.');
    }


    private function authorizeAssignedWorkOrder(WorkOrder $workOrder): void
    {
        $user = auth()->user();

        if ($user->role?->name === 'admin') {
            return;
        }

        if ((int) $workOrder->assigned_to !== (int) $user->id) {
            abort(403);
        }
    }

}
