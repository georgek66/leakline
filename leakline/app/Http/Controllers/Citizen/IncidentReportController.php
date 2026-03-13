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
use Illuminate\Support\Facades\Storage;
class IncidentReportController extends Controller
{
    /**
     * Generate a unique ticket ID for incident tracking.
     */
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
        // Validate citizen input, coordinates, and optional media attachments.
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
                // Intentionally blank to avoid duplicate/default validation text in UI.
                'location.required' => '',
                'latitude.required'  => 'Please click the map to place a pin before submitting.',
                'longitude.required' => '',
                'consent.required_with' => 'Please accept consent if you provide contact details.',

            ]
        );

        // Create primary incident record.
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

        // Keep PostGIS geometry column in sync with submitted lat/lng.
        DB::statement(
            "UPDATE incidents
            SET location_geom = ST_SetSRID(ST_MakePoint(?, ?), 4326)
            WHERE id = ?",
            [
                $validated['longitude'], // X (longitude)
                $validated['latitude'],  // Y (latitude)
                $incident->id
            ]
        );


        // Store contact row only when contact data exists (or consent was explicitly sent).
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
    public function trackForm(Request $request)
    {
        return view('citizen.incidents.track',[
            'ticket_id' => $request->query('ticket_id'),
        ]);
    }
    public function trackResult(Request $request)
    {
        $data = $request->validate([
            'ticket_id' => ['required', 'string', 'max:30'],
        ]);

        $incident = Incident::with(['contact','category','severity'])
            ->where('ticket_id', $data['ticket_id'])
            ->first();

        return view('citizen.incidents.track', [
            'ticket_id' => $data['ticket_id'],
            'incident'  => $incident,
        ]);
    }

    // Delete contact info if requested
    public function destroyByToken(string $token)
    {
        $contact = IncidentContact::where('gdpr_token', $token)->firstorFail();

        // Make contact info null
        $contact ->update([
            'name'=>null,
            'email'=>null,
            'phone'=>null,
        ]);

        return back()->with('status','Your contact info was deleted.');
    }

    public function storeSync(Request $request)
    {

        $validator = \Validator::make($request->all(),[
            'client_id'     => ['required', 'uuid'],
            'contact_name'  => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'consent'       => ['nullable', 'boolean'],
            'category_id'   => ['required', 'exists:categories,id'],
            'severity_id'   => ['required', 'exists:severity_levels,id'],
            'latitude'      => ['required', 'numeric', 'between:-90,90'],
            'longitude'     => ['required', 'numeric', 'between:-180,180'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'media64'       => ['nullable', 'array', 'max:5'],
            'media64.*'     => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Return only validated values
        $validated = $validator->validated();

        // firstOrCreate prevents duplicate if SW retries
        $incident = Incident::firstOrCreate(
            ['client_id' => $validated['client_id']],
            [
                'ticket_id'   => $this->generateTicketId(),
                'reporter_id' => auth()->id(),
                'category_id' => $validated['category_id'],
                'severity_id' => $validated['severity_id'],
                'description' => $validated['description'] ?? null,
                'latitude'    => $validated['latitude'],
                'longitude'   => $validated['longitude'],
                'status'      => 'open',
                'location' => $validated['location'] ?? $validated['latitude'] . ', ' . $validated['longitude'],
            ]
        );

        // Only run geom update if this is a brand new incident
        if ($incident->wasRecentlyCreated) {
            DB::statement(
                "UPDATE incidents SET location_geom = ST_SetSRID(ST_MakePoint(?, ?), 4326) WHERE id = ?",
                [$validated['longitude'], $validated['latitude'], $incident->id]
            );

            // Save contact details only when at least one field is provided.
            $hasContact = !empty($validated['contact_name'] ?? null) ||
                !empty($validated['contact_email'] ?? null) ||
                !empty($validated['contact_phone'] ?? null);

            if ($hasContact) {
                IncidentContact::create([
                    'incident_id'      => $incident->id,
                    'name'             => $validated['contact_name'] ?? null,
                    'email'            => $validated['contact_email'] ?? null,
                    'phone'            => $validated['contact_phone'] ?? null,
                    'preferred_locale' => app()->getLocale(),
                    'consent_version'  => 'v1',
                    'consented_at'     => !empty($validated['consent']) ? now() : null,
                    'gdpr_token'       => (string) Str::uuid(),
                ]);
            }
            if (!empty($validated['media64'])) {
                foreach ($validated['media64'] as $base64) {
                    \Log::info('base64 prefix', ['prefix' => substr($base64, 0, 80)]);
                    //  extract mime type
                    $mime = explode(';', explode(':', $base64)[1])[0];
                    //  detect image or video
                    $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                    $ext = explode('/', $mime)[1]; // extension
                    //  generate filename with correct extension
                    $filename = Str::uuid(). '.' . $ext;
                    //  decode base64
                    $pureBase64 = explode(',', $base64)[1];
                    $fileData   = base64_decode($pureBase64);
                    //  save to storage
                    $path = 'incident_media/' . $filename;
                    Storage::disk('public')->put($path, $fileData);
                    //  create IncidentMedia record
                    IncidentMedia::create([
                        'incident_id' => $incident->id,
                        'file_url'    => $path,
                        'media_type'  => $type,
                    ]);
                }
            }
        }

        // Always return JSON (SW can't handle redirects)
        return response()->json([
            'ticket_id' => $incident->ticket_id,
            'created'   => $incident->wasRecentlyCreated,
        ], 201);
    }
}
