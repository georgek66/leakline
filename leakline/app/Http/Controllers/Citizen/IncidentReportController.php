<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\IncidentContact;
use App\Models\IncidentMedia;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SeverityLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncidentReportController extends Controller
{
    private function generateTicketId(): string
    {
        do {
            $ticket = 'LL-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
        } while (Incident::where('ticket_id', $ticket)->exists());

        return $ticket;
    }
    public function received(string $ticket)
    {
        $incident = Incident::where('ticket_id', $ticket)->firstOrFail();
        return view('citizen.incidents.received', compact('incident'));
    }


    public function create()
    {
        $categories = Category::all();
        $severities = SeverityLevel::all();

        return view('citizen.incidents.create', compact('categories', 'severities'));
    }

    public function store(Request $request)
    {
        //Validate input
        $validated = $request->validate(
            [
                'contact_name'  => ['nullable', 'string', 'max:120'],
                'contact_email' => ['nullable', 'email', 'max:191'],
                'contact_phone' => ['nullable', 'string', 'max:40'],

                'consent' => ['required_with:contact_name,contact_email,contact_phone', 'boolean'],

                'category_id' => ['required', 'exists:categories,id'],
                'severity_id' => ['required', 'exists:severity_levels,id'],
                'latitude'    => ['required', 'numeric', 'between:-90,90'],
                'longitude'   => ['required', 'numeric', 'between:-180,180'],
                'description' => ['nullable', 'string', 'max:2000'],
                'location'    => ['required', 'string', 'max:255'],
                'media'       => ['nullable', 'array', 'max:5'],
                'media.*'     => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,mp4,mov'],

            ],
            [
                'location.required' => '',
                'latitude.required'  => 'Please click the map to place a pin before submitting.',
                'longitude.required' => '',
                'consent.required_with' => 'Please accept consent if you provide contact details.',

            ]
        );

        //Save the incident
        $incident = Incident::create([
            'ticket_id'   => $this->generateTicketId(),
            'reporter_id' => auth()->id(), //if logged in
            'category_id' => $validated['category_id'],
            'severity_id' => $validated['severity_id'],
            'description' => $validated['description'] ?? null,
            'latitude'   => $validated['latitude'],
            'longitude'  => $validated['longitude'],
            'status' => 'open',
            'location' => $validated['location'],

        ]);

        DB::statement(
            "UPDATE incidents
            SET location_geom = ST_SetSRID(ST_MakePoint(?, ?), 4326)
            WHERE id = ?",
            [
                $validated['longitude'], // X
                $validated['latitude'],  // Y
                $incident->id
            ]
        );


        $hasAnyContact =
            !empty($validated['contact_name'] ?? null) ||
            !empty($validated['contact_email'] ?? null) ||
            !empty($validated['contact_phone'] ?? null);

        if ($hasAnyContact || !empty($validated['consent'] ?? null)) {
            IncidentContact::create([
                'incident_id' => $incident->id,
                'name'  => $validated['contact_name'] ?? null,
                'email' => $validated['contact_email'] ?? null,
                'phone' => $validated['contact_phone'] ?? null,

                'preferred_locale' => app()->getLocale(),
                'consent_version'  => 'v1',
                'consented_at'     => !empty($validated['consent']) ? now() : null,
                'gdpr_token'       => (string) Str::uuid(),
            ]);
        }

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

        return redirect()->route('citizen.report.received', $incident->ticket_id);

    }
    public function trackForm()
    {
        return view('citizen.incidents.track');
    }
    public function trackResult(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => ['required', 'string', 'max:30'],
        ]);

        $incident = Incident::where('ticket_id', $data['ticket_id'])->first();

        return view('citizen.incidents.track', [
            'ticket_id' => $data['ticket_id'],
            'incident'  => $incident,
        ]);
    }
}
