<x-app-layout>
    <x-slot name="title">Coordinator Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Coordinator Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-lg">Intake Queue</h3>
                    <form method="GET" class="mb-4">
                        <label class="text-sm text-gray-600 mr-2">Sort by:</label>
                        <select name="sort" class="border rounded-lg px-3 pr-8 py-2 text-sm" onchange="this.form.submit()">
                            <option value="age" @selected(($sort ?? 'age') === 'age')>Newest</option>
                            <option value="sla_risk" @selected(($sort ?? 'sla_risk') === 'sla_risk')>SLA risk</option>
                        </select>
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
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($incidents as $i)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">#{{ $i->id }}</td>
                                    <td class="py-2 pr-4">{{ $i->ticket_id ?? $i->id }}</td>
                                    <td class="py-2 pr-4">{{ $i->status }}</td>
                                    <td class="py-2 pr-4">{{ $i->severity?->name ?? "-" }}</td>
                                    <td class="py-2 pr-4">{{ $i->created_at?->format('Y-m-d H:i') }}</td>

                                    @php
                                        $minutesLeft = $i->slaMinutesLeft();

                                        $abs = is_null($minutesLeft) ? null : abs($minutesLeft);
                                        $days = $abs ? intdiv($abs, 1440) : 0;
                                        $hours = $abs ? intdiv($abs % 1440, 60) : 0;
                                        $mins = $abs ? ($abs % 60) : 0;


                                        // If we dont have sla timers ,show nothing
                                        if ($abs === null) {
                                            $pretty = null;
                                        } else {
                                            // If it's at least 1 day, show: "Xd Yh"
                                            if ($days > 0) {
                                                $pretty = "{$days}d {$hours}h";
                                            }
                                            // Else if it's at least 1 hour, show: "Xh Ym"
                                            elseif ($hours > 0) {
                                                $pretty = "{$hours}h {$mins}m";
                                            }
                                            // Else show only minutes: "Xm"
                                            else {
                                                $pretty = "{$mins}m";
                                            }
                                        }
                                    @endphp

                                    <td class="py-2 pr-4">{{ $i->responseDueAt()?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $i->resolutionDueAt()?->format('Y-m-d H:i') ?? '—' }}</td>

                                    <td class="py-2 pr-4">
                                        @if (is_null($minutesLeft))
                                            —
                                        @elseif ($minutesLeft < 0)
                                            <span class="text-red-600 font-semibold">Overdue ({{ $pretty }})</span>
                                        @elseif ($minutesLeft <= 60)
                                            <span class="text-orange-600 font-semibold">{{ $pretty }} left</span>
                                        @else
                                            <span class="text-green-700">{{ $pretty }} left</span>
                                        @endif
                                    </td>



                                </tr>
                            @empty
                                <tr>
                                    <td class="py-2 text-gray-500" colspan="8">No incidents yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{--Navigation bar for more than 10 incidents--}}
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


    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Leaflet loaded?', typeof L !== 'undefined');
            console.log('map div exists?', !!document.getElementById('map'));

            const map = L.map('map').setView([35.1856, 33.3823], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const incidents = @json($incidentMarkers);
            console.log('isArray?', Array.isArray(incidents), 'count:', incidents?.length);

            const bounds = [];

            incidents.forEach(i => {
                const lat = Number(i.lat);
                const lng = Number(i.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                L.marker([lat, lng]).addTo(map)
                    .bindPopup(`
                    <strong>Ticket # ${i.id}</strong><br/>
                    Status: ${i.status}<br/>

                    `);

                bounds.push([lat, lng]);
            });

            if (bounds.length) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        });
    </script>



</x-app-layout>
