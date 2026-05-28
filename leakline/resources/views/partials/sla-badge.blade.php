@php
    // Retrieve the remaining SLA minutes for response and resolution times
    $responseMins = $incident->slaMinutesLeft();
    $resolutionMins = $incident->slaResolutionMinutesLeft();

    // Function to format minutes for better display
    $formatTime = function($abs) {
        if (is_null($abs)) return null;
        $abs = abs($abs); // Get the absolute value to handle both positive and negative numbers
        $days  = intdiv($abs, 1440); // 1440 minutes = 24 hours
        $hours = intdiv($abs % 1440, 60); // Get remaining hours after days
        $mins  = $abs % 60; // Get remaining minutes after hours

        // Return formatted string
        if ($days > 0)  return "{$days}d {$hours}h";
        if ($hours > 0) return "{$hours}h {$mins}m";
        return "{$mins}m";
    };

    // Format the SLA timers
    $responseLabel     = $formatTime($responseMins);
    $resolutionLabel   = $formatTime($resolutionMins);

    // Determine if SLA times are overdue (negative values indicate SLA breach)
    $responseOverdue   = !is_null($responseMins)   && $responseMins < 0;
    $resolutionOverdue = !is_null($resolutionMins) && $resolutionMins < 0;
@endphp

@if($incident->slaRule)
    <!-- Display SLA status badges only if an SLA rule is assigned to this incident -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        {{-- Response timer --}}
        <!-- Response SLA badge: Shows time remaining until response is due -->
        <div class="rounded-lg border p-4 {{ $responseOverdue ? 'border-red-300 bg-red-50' : 'border-amber-200 bg-amber-50' }}">
            <p class="text-xs text-gray-500 mb-1">Response Due</p>
            <p class="font-semibold {{ $responseOverdue ? 'text-red-600' : 'text-amber-700' }}">
                {{-- Show warning if overdue, otherwise show remaining time --}}
                {{ $responseOverdue ? ' Overdue by ' . $responseLabel : ' ' . $responseLabel . ' left' }}
            </p>
            <!-- Display the actual due date and time -->
            <p class="text-xs text-gray-400 mt-1">{{ $incident->responseDueAt()?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>

        {{-- Resolution timer --}}
        <!-- Resolution SLA badge: Shows time remaining until resolution is due -->
        <div class="rounded-lg border p-4 {{ $resolutionOverdue ? 'border-red-300 bg-red-50' : 'border-green-200 bg-green-50' }}">
            <p class="text-xs text-gray-500 mb-1">Resolution Due</p>
            <p class="font-semibold {{ $resolutionOverdue ? 'text-red-600' : 'text-green-700' }}">
                {{-- Show warning if overdue, otherwise show remaining time with checkmark --}}
                {{ $resolutionOverdue ? ' Overdue by ' . $resolutionLabel : ' ' . $resolutionLabel . ' left' }}
            </p>
            <!-- Display the actual due date and time -->
            <p class="text-xs text-gray-400 mt-1">{{ $incident->resolutionDueAt()?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>

    </div>
@endif
