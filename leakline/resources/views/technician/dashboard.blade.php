<x-app-layout>
    <x-slot name="title">Technician Inbox</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Technician Dashboard
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-green-100 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-red-100 p-4 text-sm text-red-800">
                {{ $errors->first() }}
            </div>
        </div>
    @endif

    @php

        $statusClass = function (?string $status): string {
            return match ($status) {
                'queued' => 'bg-gray-100 text-gray-700',
                'assigned' => 'bg-blue-100 text-blue-700',
                'in_progress' => 'bg-indigo-100 text-indigo-700',
                'on_hold' => 'bg-amber-100 text-amber-700',
                'done' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700',
            };
        };

        $fieldStatusClass = function (?string $fieldStatus): string {
            return match ($fieldStatus) {
                'on_route' => 'bg-sky-100 text-sky-700',
                'on_site' => 'bg-emerald-100 text-emerald-700',
                default => 'bg-gray-100 text-gray-600',
            };
        };

    @endphp

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">


            {{-- Inbox --}}
            <section class="space-y-3">
                <h3 class="text-lg font-semibold text-gray-900">Job Inbox</h3>

                @forelse ($inboxWorkOrders as $workOrder)
                    <article class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Job #{{ $workOrder->id }}</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $workOrder->incident?->ticket_id ?? 'Incident #' . $workOrder->incident_id }}
                                </p>
                            </div>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass($workOrder->status) }}">
                                {{ str_replace('_', ' ', $workOrder->status) }}
                            </span>
                        </div>

                        <div class="mt-3 text-sm text-gray-600">
                            Severity: <span class="font-medium text-gray-900">{{ $workOrder->incident?->severity?->name ?? '-' }}</span>
                            @if($workOrder->due_date)
                                <span class="mx-2">|</span>
                                Due: <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($workOrder->due_date)->format('d-m-Y H:i') }}</span>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <form method="POST" action="{{ route('technician.workorders.accept', ['workOrder' => $workOrder->id]) }}">
                                @csrf
                                <button class="w-full rounded-md bg-sky-100 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-200">
                                    Accept
                                </button>
                            </form>

                            <form method="POST" action="{{ route('technician.workorders.decline', ['workOrder' => $workOrder->id]) }}" class="sm:col-span-3">
                                @csrf
                                <textarea
                                    name="reason"
                                    rows="2"
                                    required
                                    minlength="10"
                                    class="w-full rounded-md border-gray-300 text-sm"
                                    placeholder="Reason for rejection (required, min 10 chars)"></textarea>

                                <button class="mt-2 w-full rounded-md border border-red-600 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                                    Reject with Reason
                                </button>
                            </form>


                            <a href="{{ route('technician.workorders.show', ['workOrder' => $workOrder->id]) }}"
                               class="inline-flex w-full items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                Details
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">
                        No pending inbox jobs.
                    </div>
                @endforelse
            </section>

            {{-- Active jobs --}}
            <section class="space-y-3">
                <h3 class="text-lg font-semibold text-gray-900">Active Jobs</h3>

                @forelse ($activeWorkOrders as $workOrder)
                    <article class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Job #{{ $workOrder->id }}</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $workOrder->incident?->ticket_id ?? 'Incident #' . $workOrder->incident_id }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass($workOrder->status) }}">
                                    {{ str_replace('_', ' ', $workOrder->status) }}
                                </span>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $fieldStatusClass($workOrder->field_status) }}">
                                    {{ $workOrder->field_status ? str_replace('_', ' ', $workOrder->field_status) : 'not set' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <form method="POST" action="{{ route('technician.workorders.field-status', ['workOrder' => $workOrder->id]) }}">
                                @csrf
                                <input type="hidden" name="field_status" value="on_route">
                                <button class="w-full rounded-md border border-sky-600 bg-white px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50">
                                    On route
                                </button>
                            </form>

                            <form method="POST" action="{{ route('technician.workorders.field-status', ['workOrder' => $workOrder->id]) }}">
                                @csrf
                                <input type="hidden" name="field_status" value="on_site">
                                <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    On site
                                </button>
                            </form>

                            <a href="{{ route('technician.workorders.show', ['workOrder' => $workOrder->id]) }}"
                               class="inline-flex w-full items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                Close Ticket/Update
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">
                        No active jobs right now.
                    </div>
                @endforelse
            </section>

            {{-- Completed / Closed Incidents --}}
            <section class="space-y-3">
                <h3 class="text-lg font-semibold text-gray-900">Completed Incidents</h3>

                @forelse ($completedWorkOrders as $workOrder)
                    <article class="rounded-xl bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Job #{{ $workOrder->id }}</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $workOrder->incident?->ticket_id ?? 'Incident #' . $workOrder->incident_id }}
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    Technician: {{ $workOrder->technician?->name ?? 'Unassigned' }}
                                </p>
                            </div>
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusClass($workOrder->status) }}">
                    {{ str_replace('_', ' ', $workOrder->status) }}
                </span>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('technician.workorders.show', ['workOrder' => $workOrder->id]) }}"
                               class="inline-flex w-full items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                View Details
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">
                        No completed or closed incidents.
                    </div>
                @endforelse
            </section>

        </div>
    </div>
</x-app-layout>
