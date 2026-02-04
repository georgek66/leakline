@extends('layouts.home')

@section('content')
    <div class="max-w-3xl mx-auto p-6">
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h1 class="text-2xl font-bold">
                {{ __('citizen.report_received_title') }}
            </h1>

            <p class="mt-2 text-gray-600">
                {{ __('citizen.report_received_message') }}
            </p>

            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="text-sm text-green-800">
                    {{ __('citizen.ticket_id_label') }}
                </p>

                <p class="text-2xl font-mono font-semibold text-green-900">
                    {{ $incident->ticket_id }}
                </p>

                <p class="text-xs text-green-800 mt-2">
                    {{ __('citizen.ticket_id_help') }}
                </p>
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('citizen.track.form') }}"
                   class="px-4 py-2 rounded-lg border">
                    {{ __('citizen.track_report') }}
                </a>

                <a href="{{ route('citizen.report.create') }}"
                   class="px-4 py-2 rounded-lg border">
                    {{ __('citizen.report_another') }}
                </a>
            </div>
        </div>
    </div>
@endsection
