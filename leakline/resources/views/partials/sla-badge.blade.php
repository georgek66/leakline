@php
    $responseMins = $incident->slaMinutesLeft();
    $resolutionMins = $incident->slaResolutionMinutesLeft();

    $formatTime = function($abs) {
        if (is_null($abs)) return null;
        $abs = abs($abs);
        $days  = intdiv($abs, 1440);
        $hours = intdiv($abs % 1440, 60);
        $mins  = $abs % 60;
        if ($days > 0)  return "{$days}d {$hours}h";
        if ($hours > 0) return "{$hours}h {$mins}m";
        return "{$mins}m";
    };

    $responseLabel     = $formatTime($responseMins);
    $resolutionLabel   = $formatTime($resolutionMins);
    $responseOverdue   = !is_null($responseMins)   && $responseMins < 0;
    $resolutionOverdue = !is_null($resolutionMins) && $resolutionMins < 0;
@endphp

@if($incident->slaRule)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

        {{-- Response timer --}}
        <div class="rounded-lg border p-4 {{ $responseOverdue ? 'border-red-300 bg-red-50' : 'border-amber-200 bg-amber-50' }}">
            <p class="text-xs text-gray-500 mb-1">Response Due</p>
            <p class="font-semibold {{ $responseOverdue ? 'text-red-600' : 'text-amber-700' }}">
                {{ $responseOverdue ? '⚠️ Overdue by ' . $responseLabel : '⏱ ' . $responseLabel . ' left' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $incident->responseDueAt()?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>

        {{-- Resolution timer --}}
        <div class="rounded-lg border p-4 {{ $resolutionOverdue ? 'border-red-300 bg-red-50' : 'border-green-200 bg-green-50' }}">
            <p class="text-xs text-gray-500 mb-1">Resolution Due</p>
            <p class="font-semibold {{ $resolutionOverdue ? 'text-red-600' : 'text-green-700' }}">
                {{ $resolutionOverdue ? '⚠️ Overdue by ' . $resolutionLabel : '✅ ' . $resolutionLabel . ' left' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $incident->resolutionDueAt()?->format('Y-m-d H:i') ?? '—' }}</p>
        </div>

    </div>
@endif
