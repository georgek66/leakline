<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentMedia;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SeverityLevel;

class IncidentReportController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        $severities = SeverityLevel::all();

        return view('citizen.incidents.create', compact('categories', 'severities'));
    }

    public function store(Request $request)
    {
        //Validate input
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'severity_id' => ['required', 'exists:severity_levels,id'],
            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:2000'],

            'media'       => ['nullable', 'array', 'max:5'],
            'media.*'     => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov'],
        ]);
        //Save the incident
        $incident = Incident::create([
            'reporter_id' => auth()->id(), //if logged in
            'category_id' => $validated['category_id'],
            'severity_id' => $validated['severity_id'],
            'description' => $validated['description'] ?? null,
            'latitude'   => $validated['latitude'],
            'longitude'  => $validated['longitude'],
            'status' => 'open',
        ]);
        //Save uploaded media
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {

                // Save file to storage/app/public/incident_media
                $path = $file->store('incident_media', 'public');

                // Detect type from mime
                $mime = $file->getMimeType(); // e.g. image/jpeg, video/mp4
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';

                // Create DB record linked to incident
                IncidentMedia::create([
                    'incident_id' => $incident->id,
                    'file_url'    => $path,   // e.g. incident_media/abc.jpg
                    'media_type'  => $type,   // image | video
                ]);
            }
        }

        return redirect()
            ->route('citizen.report.create')
            ->with('success', 'Ticket saved with media!');
    }
}
