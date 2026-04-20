<x-app-layout>
    <x-slot name="title">Coordinator Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Coordinator Dashboard
        </h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    @endpush

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg">Intake Queue</h3>

                    <form method="GET" class="mb-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-600">Status:</label>
                            <select name="sort_status" class="border rounded-lg px-3 pr-8 py-2 text-sm" onchange="this.form.submit()">
                                <option value="" @selected(empty($status))>All statuses</option>
                                @foreach($statuses as $st)
                                    <option value="{{ $st }}" @selected(($status ?? '') === $st)>
                                        {{ ucwords(str_replace('_', ' ', $st)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2 ml-auto">
                            <label class="text-sm text-gray-600">Sort by:</label>
                            <select name="sort" class="border rounded-lg px-3 pr-8 py-2 text-sm" onchange="this.form.submit()">
                                <option value="age" @selected(($sort ?? 'age') === 'age')>Newest</option>
                                <option value="sla_risk" @selected(($sort ?? 'age') === 'sla_risk')>SLA risk</option>
                                <option value="severity" @selected(($sort ?? 'age') === 'severity')>Severity</option>
                            </select>
                        </div>
                    </form>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b">
                            <tr class="text-left tracking-wide text-black">
                                <th class="py-2 pr-4">ID</th>
                                <th class="py-2 pr-4">Ticket</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Severity</th>
                                <th class="py-2 pr-4">Created at:</th>
                                <th class="py-2 pr-4">Response Due:</th>
                                <th class="py-2 pr-4">Resolution Due:</th>
                                <th class="py-2 pr-4">SLA Risk:</th>
                                <th class="py-2 pr-4">Duplicates:</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($incidents as $i)
                                @php
                                    $isCancelled = $i->status === 'cancelled';
                                    $minutesLeft = $isCancelled ? null : $i->slaMinutesLeft();

                                    // Make each status to the same badge colors used on the incident details page.
                                    $rowStatus = $i->status ?? 'unknown';
                                    $statusBadgeClass = match ($rowStatus) {
                                        'open' => 'bg-blue-100 text-blue-700',
                                        'assigned' => 'bg-indigo-100 text-indigo-700',
                                        'in_progress' => 'bg-amber-100 text-amber-700',
                                        'resolved' => 'bg-green-100 text-green-700',
                                        'closed' => 'bg-gray-100 text-gray-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };

                                    // Show minutes in a more friendly way, e.g. "1d 3h left" instead of "1800 minutes left".
                                    // For overdue incidents, show how long ago they were due, e.g. "Overdue (2h 15m ago)".
                                    $abs = is_null($minutesLeft) ? null : abs($minutesLeft);
                                    $days = $abs ? intdiv($abs, 1440) : 0;
                                    $hours = $abs ? intdiv($abs % 1440, 60) : 0;
                                    $mins = $abs ? ($abs % 60) : 0;

                                    if ($abs === null) {
                                        $pretty = null;
                                    } elseif ($days > 0) {
                                        $pretty = "{$days}d {$hours}h";
                                    } elseif ($hours > 0) {
                                        $pretty = "{$hours}h {$mins}m";
                                    } else {
                                        $pretty = "{$mins}m";
                                    }
                                @endphp

                                <tr class="border-b">
                                    <td class="py-2 pr-4">#{{ $i->id }}</td>
                                    <td class="py-2 pr-4">{{ $i->ticket_id ?? $i->id }}</td>
                                    <td class="py-2 pr-4">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadgeClass }}">
                                        {{ str_replace('_', ' ', $rowStatus) }}
                                    </span>
                                    </td>

                                    <td class="py-2 pr-4">{{ $isCancelled ? '-' : ($i->severity?->name ?? '-') }}</td>
                                    <td class="py-2 pr-4">{{ $isCancelled ? '-' : ($i->created_at?->format('d-m-Y H:i') ?? '-') }}</td>

                                    <td class="py-2 pr-4">{{ $isCancelled ? '-' : ($i->responseDueAt()?->format('d-m-Y H:i') ?? '—') }}</td>
                                    <td class="py-2 pr-4">{{ $isCancelled ? '-' : ($i->resolutionDueAt()?->format('d-m-Y H:i') ?? '—') }}</td>

                                    <td class="py-2 pr-4">
                                        @if($isCancelled)
                                            <span class="text-gray-500">-</span>
                                        @elseif (is_null($minutesLeft))
                                            —
                                        @elseif ($minutesLeft < 0)
                                            <span class="text-red-600 font-semibold">Overdue ({{ $pretty }})</span>
                                        @elseif ($minutesLeft <= 60)
                                            <span class="text-orange-600 font-semibold">{{ $pretty }} left</span>
                                        @else
                                            <span class="text-green-700">{{ $pretty }} left</span>
                                        @endif
                                    </td>

                                    <td class="py-2 pr-4">
                                        @if($isCancelled)
                                            <span class="text-gray-500">-</span>
                                        @elseif(($i->duplicates_count ?? 0) > 0)
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">
                                                    {{ $i->duplicates_count }} possible
                                                </span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>

                                    <td class="py-2 pr-4">
                                        <a href="{{ route('coordinator.incidents.show', $i) }}"
                                           class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="py-2 text-gray-500" colspan="10">No incidents yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $incidents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow">
        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Incidents Map</h2>
        </div>

        <div class="p-6">
            <div id="map" class="h-[420px] w-full rounded-lg"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                console.log('Leaflet loaded?', typeof L !== 'undefined');
                console.log('map div exists?', !!document.getElementById('map'));

                const map = L.map('map').setView([35.1856, 33.3823], 12);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                const incidents = @json($incidentMarkers ?? []);
                if (!Array.isArray(incidents)) return;
                console.log('isArray?', Array.isArray(incidents), 'count:', incidents?.length);

                const clusterGroup = L.markerClusterGroup({
                    chunkedLoading: true,
                    showCoverageOnHover: false,
                    maxClusterRadius: 50
                });
                const bounds = [];

                // Build markers only for valid coordinates to avoid Leaflet errors. If lat/lng are invalid, skip that incident.
                incidents.forEach(i => {
                    const lat = Number(i.lat);
                    const lng = Number(i.lng);
                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    const marker = L.marker([lat, lng]).bindPopup(`
                        <strong>Ticket # ${i.id}</strong><br/>
                        Status: ${i.status}<br/>
                    `);

                    clusterGroup.addLayer(marker);
                    bounds.push([lat, lng]);
                });

                map.addLayer(clusterGroup);

                if (bounds.length) {
                    map.fitBounds(L.latLngBounds(bounds), { padding: [30, 30] });
                }
            });
        </script>
    @endpush
</x-app-layout>
