<x-app-layout>
    <x-slot name="title">Analytics and Reporting</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Analytics and Reporting
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

           <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Total Incidents -->
                <div class="rounded-lg bg-white p-6 shadow-sm border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600 font-medium">Total Incidents</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $totalIncidents }}</p>
                </div>

                <!-- Open Incidents -->
                <div class="rounded-lg bg-white p-6 shadow-sm border-l-4 border-red-500">
                    <p class="text-sm text-gray-600 font-medium">Open Incidents</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ $openIncidents }}</p>
                </div>

                <!-- Resolved Incidents -->
                <div class="rounded-lg bg-white p-6 shadow-sm border-l-4 border-green-500">
                    <p class="text-sm text-gray-600 font-medium">Resolved</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $resolvedIncidents }}</p>
                </div>

                <!-- MTTR -->
                <div class="rounded-lg bg-white p-6 shadow-sm border-l-4 border-purple-500">
                    <p class="text-sm text-gray-600 font-medium">Avg MTTR</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $mttrHours ? number_format($mttrHours, 1) : 'N/A' }}</p>
                    <p class="text-xs text-gray-500 mt-1">hours</p>
                </div>

                <!-- Recent (30 days) -->
                <div class="rounded-lg bg-white p-6 shadow-sm border-l-4 border-yellow-500">
                    <p class="text-sm text-gray-600 font-medium">Last 30 Days</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-2">{{ $recentIncidents }}</p>
                </div>
            </div>

            <!-- Status Breakdown -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Status Breakdown</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Incidents</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Percentage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($statuses as $statusData)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-gray-800 font-medium">{{ ucfirst($statusData->status) }}</td>
                                <td class="text-right py-3 px-4 text-gray-800 font-semibold">{{ $statusData->total }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $totalIncidents > 0 ? number_format(($statusData->total / $totalIncidents) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Incidents by Severity -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Incidents by Severity</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Severity Level</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Incidents</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Percentage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($incidentsBySeverity as $severity)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-gray-800 font-medium">{{ $severity->name }}</td>
                                <td class="text-right py-3 px-4 text-gray-800 font-semibold">{{ $severity->total }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $totalIncidents > 0 ? number_format(($severity->total / $totalIncidents) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Incidents by Area -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Incidents by Area</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Area</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Incidents</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Percentage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($incidentsByArea as $area)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-gray-800 font-medium">{{ $area->area_name }}</td>
                                <td class="text-right py-3 px-4 text-gray-800 font-semibold">{{ $area->total }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $totalIncidents > 0 ? number_format(($area->total / $totalIncidents) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Seasonal Trends -->
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Seasonal Trends</h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b-2 border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Season</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Incidents</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Percentage</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($seasonalTrends as $season)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <td class="py-3 px-4 text-gray-800 font-medium">{{ $season->season }}</td>
                                <td class="text-right py-3 px-4 text-gray-800 font-semibold">{{ $season->total }}</td>
                                <td class="text-right py-3 px-4 text-gray-600">{{ $totalIncidents > 0 ? number_format(($season->total / $totalIncidents) * 100, 1) : 0 }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No data available</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <!-- Download as PDF Button -->
            <div class="mb-4">
                <a href="{{ route('coordinator.reports.download') }}"
                   class="inline-block rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                    Download as PDF
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
