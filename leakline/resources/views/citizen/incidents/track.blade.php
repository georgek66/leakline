@extends('layouts.home')

@section('content')
    <div class="max-w-3xl mx-auto p-6">
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h1 class="text-2xl font-bold">
                {{ __('citizen.track_title') }}
            </h1>

            <p class="mt-2 text-gray-600">
                {{ __('citizen.track_help') }}
            </p>

            <form method="POST" action="{{ route('citizen.track.search') }}" class="mt-5">
                @csrf

                <label class="block text-sm font-medium mb-1">
                    {{ __('citizen.ticket_id') }}
                </label>

                <input
                    type="text"
                    name="ticket_id"
                    value="{{ old('ticket_id', $ticket_id ?? '') }}"
                    class="w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                    placeholder="LL-2026-ABC123"
                >

                @error('ticket_id')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror

                <button class="px-4 py-2 rounded-lg border">
                    {{ __('citizen.track_button') }}
                </button>
            </form>

            @isset($incident)
                <div class="mt-6 border-t pt-6">
                    @if($incident)
                        <div class="rounded-xl border bg-gray-50 p-4 space-y-1">
                            <p>
                                <strong>{{ __('citizen.status') }}:</strong>
                                {{ ucfirst($incident->status) }}
                            </p>
                            <p>
                                <strong>{{ __('citizen.category') }}:</strong>
                                {{ optional($incident->category)->name }}
                            </p>
                            <p>
                                <strong>{{ __('citizen.severity') }}:</strong>
                                {{ optional($incident->severity)->name }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('citizen.reported_at') }}:
                                {{ $incident->created_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    @else
                        <p class="text-red-600">
                            {{ __('citizen.track_not_found') }}
                        </p>
                    @endif
                </div>
            @endisset
        </div>
    </div>
@endsection
