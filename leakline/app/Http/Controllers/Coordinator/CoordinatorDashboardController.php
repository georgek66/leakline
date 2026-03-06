<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;
class CoordinatorDashboardController extends Controller
{
    public function index(Request $request)
    {

        $sort = $request->query('sort','age');
        $q= Incident::query()->with(['severity', 'slaRule']);

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

        return view('coordinator.dashboard', compact('incidents', 'sort', 'incidentMarkers'));
    }

    // Find possible duplicates
//    public function duplicates(Incident $incident)
//    {
//
//        $distanceMeters = 100;
//        $hours = 24;
//
//        $target = Incident::query()
//            ->select('incidents.*')
//            ->selectRaw('ST_Y(location_geom::geometry) as lat')
//            ->selectRaw('ST_X(location_geom::geometry) as lng')
//            ->whereKey($incident->id)
//            ->whereNotNull('location_geom')
//            ->firstOrFail();
//
//        $candidates = Incident::query()
//            ->select('incidents.*')
//            ->selectRaw('ST_Y(location_geom::geometry) as lat')
//            ->selectRaw('ST_X(location_geom::geometry) as lng')
//            ->selectRaw(
//                "ST_Distance(incidents.location_geom::geography, ?::geography) as meters",
//                [$target->location_geom]
//            )
//            ->whereKeyNot($target->id)
//            ->whereNotNull('location_geom')
//            ->where('created_at', '>=',now()->subHours($hours))
//            ->whereNotIn('status',['closed','resolved'])
//            ->whereRaw(
//                "ST_DWithin(incidents.location_geom::geography, ?::geography, ?)",
//                [$target->location_geom,$distanceMeters]
//            )
//            ->orderBy('meters')
//            ->limit(10)
//            ->get();
//
//        return view('coordinator.incidents.duplicates',compact('target','candidates','distanceMeters','hours'));
//
//    }



}
