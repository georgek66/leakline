<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analytics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 10px;
        }
        h1 {
            color: #0066cc;
            margin: 0;
            font-size: 24px;
        }
        .generated-date {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .metric-card {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #0066cc;
        }
        .metric-label {
            font-size: 10px;
            color: #666;
            font-weight: bold;
        }
        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #0066cc;
            margin-top: 5px;
        }
        .metric-unit {
            font-size: 9px;
            color: #999;
            margin-top: 2px;
        }
        h2 {
            font-size: 14px;
            color: #0066cc;
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #0066cc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #0066cc;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .percentage {
            color: #666;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>LeakLine Report</h1>
    <div class="generated-date">Generated on {{ now()->format('F j, Y \a\t H:i:s') }}</div>
</div>

<!-- Key Metrics -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-label">Total Incidents</div>
        <div class="metric-value">{{ $totalIncidents }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Open Incidents</div>
        <div class="metric-value">{{ $openIncidents }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Resolved</div>
        <div class="metric-value">{{ $resolvedIncidents }}</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Avg MTTR</div>
        <div class="metric-value">{{ $mttrHours ? number_format($mttrHours, 1) : 'N/A' }}</div>
        <div class="metric-unit">hours</div>
    </div>
    <div class="metric-card">
        <div class="metric-label">Last 30 Days</div>
        <div class="metric-value">{{ $recentIncidents }}</div>
    </div>
</div>

<!-- Status Breakdown -->
<h2>Status Breakdown</h2>
<table>
    <thead>
    <tr>
        <th>Status</th>
        <th class="text-right">Incidents</th>
        <th class="text-right">Percentage</th>
    </tr>
    </thead>
    <tbody>
    @forelse($statuses as $statusData)
        <tr>
            <td>{{ ucfirst($statusData->status) }}</td>
            <td class="text-right">{{ $statusData->total }}</td>
            <td class="text-right percentage">{{ $totalIncidents > 0 ? number_format(($statusData->total / $totalIncidents) * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No data available</td>
        </tr>
    @endforelse
    </tbody>
</table>
<br>
<!-- Incidents by Severity -->
<h2>Incidents by Severity</h2>
<table>
    <thead>
    <tr>
        <th>Severity Level</th>
        <th class="text-right">Incidents</th>
        <th class="text-right">Percentage</th>
    </tr>
    </thead>
    <tbody>
    @forelse($incidentsBySeverity as $severity)
        <tr>
            <td>{{ $severity->name }}</td>
            <td class="text-right">{{ $severity->total }}</td>
            <td class="text-right percentage">{{ $totalIncidents > 0 ? number_format(($severity->total / $totalIncidents) * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No data available</td>
        </tr>
    @endforelse
    </tbody>
</table>

<!-- Incidents by Area -->
<h2>Incidents by Area</h2>
<table>
    <thead>
    <tr>
        <th>Area</th>
        <th class="text-right">Incidents</th>
        <th class="text-right">Percentage</th>
    </tr>
    </thead>
    <tbody>
    @forelse($incidentsByArea as $area)
        <tr>
            <td>{{ $area->area_name }}</td>
            <td class="text-right">{{ $area->total }}</td>
            <td class="text-right percentage">{{ $totalIncidents > 0 ? number_format(($area->total / $totalIncidents) * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No data available</td>
        </tr>
    @endforelse
    </tbody>
</table>

<!-- Seasonal Trends -->
<h2>Seasonal Trends</h2>
<table>
    <thead>
    <tr>
        <th>Season</th>
        <th class="text-right">Incidents</th>
        <th class="text-right">Percentage</th>
    </tr>
    </thead>
    <tbody>
    @forelse($seasonalTrends as $season)
        <tr>
            <td>{{ $season->season }}</td>
            <td class="text-right">{{ $season->total }}</td>
            <td class="text-right percentage">{{ $totalIncidents > 0 ? number_format(($season->total / $totalIncidents) * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">No data available</td>
        </tr>
    @endforelse
    </tbody>
</table>


</body>
</html>
