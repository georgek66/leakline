<x-app-layout>
    {{-- Page title shown in browser/tab and layout title slot --}}
    <x-slot name="title">
        Incident Details
    </x-slot>

    {{-- Header area: page heading + current status badge --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Incident Details
            </h2>

            @php
                // Badge logic always has a value
                $status = $incident->status ?? 'unknown';

                // Map incident status to Tailwind badge colors
                $badgeClass = match ($status) {
                    'open' => 'bg-blue-100 text-blue-700',
                    'assigned' => 'bg-indigo-100 text-indigo-700',
                    'in_progress' => 'bg-amber-100 text-amber-700',
                    'resolved' => 'bg-green-100 text-green-700',
                    'closed' => 'bg-gray-100 text-gray-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
                // Check if we have valid coordinates for map display
                $hasCoordinates = $incident->latitude !== null && $incident->longitude !== null;
            @endphp

            {{--  in_progress -> in progress --}}
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                {{ str_replace('_', ' ', $status) }}
            </span>
        </div>
    </x-slot>

    @if(session('status'))
        <div class="mx-auto mt-4 max-w-6xl sm:px-6 lg:px-8">
            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <div class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.78-9.72a.75.75 0 00-1.06-1.06L9.25 10.69 7.78 9.22a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-black">Assignment updated</p>
                    <p class="text-sm text-black">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Quick navigation back to intake queue --}}
            <div>
                <a href="{{ route('coordinator.dashboard') }}"
                   class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                    Back to Dashboard
                </a>
            </div>

            {{-- Incident summary card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Summary</h3>

                    {{-- null-safe fallbacks to avoid broken UI --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Ticket</p>
                            <p class="font-medium text-gray-900">{{ $incident->ticket_id ?? ('#' . $incident->id) }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Incident ID</p>
                            <p class="font-medium text-gray-900">#{{ $incident->id }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Status</p>
                            <p class="font-medium text-gray-900">{{ str_replace('_', ' ', $incident->status ?? '—') }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Severity</p>
                            <p class="font-medium text-gray-900">{{ $incident->severity?->name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Category</p>
                            <p class="font-medium text-gray-900">{{ $incident->category?->name ?? '—' }}</p>
                        </div>

                        <div>
                            <p class="text-gray-500">Created At</p>
                            <p class="font-medium text-gray-900">{{ $incident->created_at?->format('Y-m-d H:i') ?? '—' }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-gray-500">Location</p>
                            <p class="font-medium text-gray-900">{{ $incident->location ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Description--}}
                    <div class="mt-6 border-t pt-4">
                        <p class="text-gray-500 text-sm mb-1">Description</p>
                        <p class="text-gray-900 whitespace-pre-line">
                            {{ $incident->description ?: 'No description provided.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Technician Assignment</h3>

                    @php
                        $fieldStatus = $currentWorkOrder?->field_status ?? null;
                        $fieldStatusLabel = match ($fieldStatus) {
                            'on_route' => 'On route',
                            'on_site' => 'On site',
                            default => 'Not set',
                        };
                        $fieldStatusBadge = match ($fieldStatus) {
                            'on_route' => 'bg-sky-100 text-sky-700',
                            'on_site' => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mb-4">
                        <div>
                            <p class="text-gray-500">Assigned Technician</p>
                            <p class="font-medium text-gray-900">{{ $currentWorkOrder?->technician?->name ?? 'Not assigned' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Technician Email</p>
                            <p class="font-medium text-gray-900">{{ $currentWorkOrder?->technician?->email ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Job Status</p>
                            <p class="font-medium text-gray-900">{{ $currentWorkOrder ? str_replace('_', ' ', $currentWorkOrder->status) : 'No job listing yet' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Field Status</p>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $fieldStatusBadge }}">
                                {{ $fieldStatusLabel }}
                            </span>
                        </div>
                    </div>

                    <p class="mb-2 text-xs text-gray-500">
                        Choose a technician based on current workload. "Available" means no active assigned/in-progress jobs.
                    </p>

                    <form method="POST" action="{{ route('coordinator.incidents.assign-technician', $incident) }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <select name="technician_id" class="w-full rounded-md border-gray-300 text-sm" required>
                            <option value="">Select technician</option>
                            @foreach($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected($currentWorkOrder?->assigned_to === $technician->id)>
                                    {{ $technician->name }} - {{ $technician->availability_label }} (Active: {{ $technician->active_jobs_count }}, Inbox: {{ $technician->inbox_jobs_count }}){{ $currentWorkOrder?->assigned_to === $technician->id ? ' — currently assigned' : '' }}
                                </option>
                            @endforeach
                            </select>

                            @if($errors->has('technician_id'))
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $errors->first('technician_id') }}</p>
                            @endif
                        </div>

                        <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Assign Technician
                        </button>
                    </form>

                    <div class="mt-4 overflow-x-auto rounded-md border border-gray-200">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Technician</th>
                                <th class="px-3 py-2 font-semibold">Availability</th>
                                <th class="px-3 py-2 font-semibold">Active Jobs</th>
                                <th class="px-3 py-2 font-semibold">Inbox</th>
                                <th class="px-3 py-2 font-semibold">In Progress</th>
                                <th class="px-3 py-2 font-semibold">Completed</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($technicians as $technician)
                                <tr class="border-t border-gray-100">
                                    <td class="px-3 py-2">
                                        <p class="font-medium text-gray-900">{{ $technician->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $technician->email ?? 'No email' }}</p>
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $technician->availability_badge }}">
                                            {{ $technician->availability_label }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 font-medium text-gray-900">{{ $technician->active_jobs_count }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $technician->inbox_jobs_count }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $technician->in_progress_jobs_count }}</td>
                                    <td class="px-3 py-2 text-gray-700">{{ $technician->completed_jobs_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-3 text-center text-gray-500">No technicians found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            @if(!empty($duplicates) && $duplicates->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Possible Duplicates</h3>

                        @foreach($duplicates as $d)
                            <div class="border rounded-md p-3 mb-2 text-sm">
                                <div><strong>#{{ $d->id }}</strong> ({{ $d->ticket_id ?? 'No ticket' }})</div>
                                <div class="text-gray-500">{{ $d->created_at?->format('d-m-Y H:i') ?? '—' }} | {{ $d->status }} | {{ $d->duplicates_count }} nearby duplicates</div>

                                <form method="POST" action="{{ route('coordinator.incidents.merge', $incident) }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="duplicate_id" value="{{ $d->id }}">

                                    <button type="submit"
                                            class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                        Merge
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif


            {{-- SLA Timers card --}}
            @if($incident->slaRule)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">SLA Timers</h3>
                        @include('partials.sla-badge', ['incident' => $incident])
                    </div>
                </div>
            @endif

            {{-- Location map card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Location Details</h3>

                    @if($hasCoordinates)

                    <div
                            id="incident-map"
                            data-lat="{{ $incident->latitude }}"
                            data-lng="{{ $incident->longitude }}"
                            data-label="{{ $incident->location ?? $incident->ticket_id ?? ('#' . $incident->id) }}"
                            style="height: 350px; border-radius: 8px;"
                        ></div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const mapEl = document.getElementById('incident-map');
                                if (!mapEl) {
                                    return;
                                }

                                const lat = Number(mapEl.dataset.lat);
                                const lng = Number(mapEl.dataset.lng);
                                if (Number.isNaN(lat) || Number.isNaN(lng)) {
                                    return;
                                }
                                const popupLabel = mapEl.dataset.label || 'Incident location';

                                const map = L.map('incident-map').setView([lat, lng], 15);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(map);

                                L.marker([lat, lng]).addTo(map).bindPopup(popupLabel).openPopup();
                            });
                        </script>
                    @else
                        <p class="text-sm text-gray-500">No map coordinates available for this incident.</p>
                    @endif
                </div>
            </div>

            {{-- Media card --}}
            @if($incident->media->count())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Media ({{ $incident->media->count() }})
                        </h3>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($incident->media as $media)
                                @if($media->media_type === 'image')
                                    <a href="{{ Storage::url($media->file_url) }}" target="_blank">
                                        <img
                                            src="{{ Storage::url($media->file_url) }}"
                                            alt="Incident media"
                                            class="w-full h-48 object-cover rounded-lg border hover:opacity-90 transition"
                                        >
                                    </a>
                                @elseif($media->media_type === 'video')
                                    <video
                                        controls
                                        class="w-full h-48 rounded-lg border"
                                    >
                                        <source src="{{ Storage::url($media->file_url) }}" type="video/mp4">
                                        Your browser does not support video.
                                    </video>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Contact details card --}}
            @if($incident->contact)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reporter Contact</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Name</p>
                                <p class="font-medium text-gray-900">{{ $incident->contact->name ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Email</p>
                                <p class="font-medium text-gray-900">{{ $incident->contact->email ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Phone</p>
                                <p class="font-medium text-gray-900">{{ $incident->contact->phone ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Preferred Language</p>
                                <p class="font-medium text-gray-900">{{ strtoupper($incident->contact->preferred_locale ?? '—') }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Consent Version</p>
                                <p class="font-medium text-gray-900">{{ $incident->contact->consent_version ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Consented At</p>
                                <p class="font-medium text-gray-900">{{ $incident->contact->consented_at?->format('Y-m-d H:i') ?? '—' }}</p>
                            </div>
                        </div>

                        {{-- GDPR notice --}}
                        <div class="mt-4 border-t pt-4">
                            <p class="text-xs text-gray-400">
                                ⚠️ This information is personal data protected under GDPR. Handle with care.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Reporter Contact</h3>
                        <p class="text-sm text-gray-500">This incident was submitted anonymously.</p>
                    </div>
                </div>
            @endif

            {{-- Audit Trail / Timeline card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>

                    @if($incident->events->count())
                        <div class="relative">
                            {{-- Vertical line --}}
                            <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-6">
                                @foreach($incident->events->sortByDesc('created_at') as $event)
                                    <div class="relative flex items-start gap-4 pl-8">

                                        {{-- Dot on timeline --}}
                                        <div class="absolute left-0 w-4 h-4 rounded-full border-2 border-white mt-1
                                {{ match($event->event_type) {
                                    'created'        => 'bg-blue-500',
                                    'status_changed' => 'bg-amber-500',
                                    'assigned'       => 'bg-indigo-500',
                                    'comment'        => 'bg-gray-400',
                                    'media_added'    => 'bg-green-500',
                                    'merged'         => 'bg-purple-500',
                                    default          => 'bg-gray-400',
                                } }}">
                                        </div>

                                        {{-- Event content --}}
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $event->message }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-400">
                                        {{ $event->created_at?->format('Y-m-d H:i') ?? '—' }}
                                    </span>
                                                @if($event->actor_id)
                                                    <span class="text-xs text-gray-400">·</span>
                                                    <span class="text-xs text-gray-400">
                                            by {{ $event->actor?->name ?? 'Unknown' }}
                                        </span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No events recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
