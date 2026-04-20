<x-app-layout>
    <x-slot name="title">Job Listing #{{ $workOrder->id }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Job #{{ $workOrder->id }}
            </h2>
            <a href="{{ route('technician.dashboard') }}"
               class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-xs font-semibold text-white hover:bg-gray-700">
                Back to Inbox
            </a>
        </div>
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
        $statusBadge = match ($workOrder->status) {
            'assigned' => 'bg-blue-100 text-blue-700',
            'in_progress' => 'bg-indigo-100 text-indigo-700',
            'on_hold' => 'bg-amber-100 text-amber-700',
            'done' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };

        $fieldBadge = match ($workOrder->field_status) {
            'on_route' => 'bg-sky-100 text-sky-700',
            'on_site' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-600',
        };

        $isReadOnly = in_array($workOrder->status, ['done', 'cancelled'], true);


    @endphp

    <div class="py-6 sm:py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="text-xs text-gray-500">Incident</p>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $workOrder->incident?->ticket_id ?? 'Incident #' . $workOrder->incident_id }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Severity</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $workOrder->incident?->severity?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $statusBadge }}">
                            {{ str_replace('_', ' ', $workOrder->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Field Status</p>
                        <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $fieldBadge }}">
                            {{ $workOrder->field_status ? str_replace('_', ' ', $workOrder->field_status) : 'not set' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900">Incident Details</h3>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <p class="text-gray-500">Address / Location</p>
                        <p class="font-medium text-gray-900">{{ $workOrder->incident?->location ?? '—' }}</p>
                        @if($workOrder->incident && $workOrder->incident->location)
                            @php
                                $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($workOrder->incident->location);
                            @endphp

                            <a href = "{{ $mapsUrl }}"
                            target = "_blank"
                            rel="noopener noreferrer"
                               class="mt-2 inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                            Navigate
                            </a>
                        @endif
                    </div>
                    <div>
                        <p class="text-gray-500">Category</p>
                        <p class="font-medium text-gray-900">{{ $workOrder->incident?->category?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Severity</p>
                        <p class="font-medium text-gray-900">{{ $workOrder->incident?->severity?->name ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-gray-500">Description</p>
                        <p class="font-medium text-gray-900 whitespace-pre-line">{{ $workOrder->incident?->description ?: 'No description provided.' }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900">Photos / Media</h3>
                <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-3">
                    @forelse($workOrder->incident?->media ?? [] as $media)
                        @if($media->media_type === 'image')
                            <a href="{{ Storage::url($media->file_url) }}" target="_blank" class="block overflow-hidden rounded-lg border hover:opacity-90 transition">
                                <img src="{{ Storage::url($media->file_url) }}" alt="Incident photo" class="h-40 w-full object-cover">
                            </a>
                        @elseif($media->media_type === 'video')
                            <video controls class="h-40 w-full rounded-lg border object-cover">
                                <source src="{{ Storage::url($media->file_url) }}" type="video/mp4">
                                Your browser does not support video.
                            </video>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500">No photos or media attached to this incident.</p>
                    @endforelse
                </div>
            </section>

            @if(in_array($workOrder->status, ['in_progress', 'on_hold'], true))
                <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Close Ticket</h3>

                    <form method="POST"
                          action="{{ route('technician.workorders.status', ['workOrder' => $workOrder->id]) }}"
                          enctype="multipart/form-data"
                          class="mt-4 space-y-6">
                        @csrf

                        <!-- Resolution Code -->
                        <div>
                            <label class="mb-3 text-base font-semibold text-gray-900">Resolution Code *</label>
                            <select name="resolution_code_id" required class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Select resolution</option>
                                @foreach ($resolutionCodes as $code)
                                    <option value="{{ $code->id }}">{{ $code->code }} - {{ $code->label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Media -->
                        <div>
                            <label class="mb-3 text-base font-semibold text-gray-900">Add Pictures / Videos (optional)</label>
                            <input type="file" name="media[]" multiple accept="image/*,video/*" class="w-full rounded-md border-gray-300 text-sm">
                            <p class="mt-1 text-xs text-gray-500">You can upload multiple files.</p>
                        </div>

                        <!-- Materials -->
                        <div>
                            <h4 class="mb-3 text-base font-semibold text-gray-900">Materials Used</h4>
                            <div class="space-y-3">
                                @for ($i = 0; $i < 3; $i++)
                                    <div class="rounded-md border p-3">
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                                            <input type="text" name="materials_new[{{ $i }}][item_name]" placeholder="Item name" class="w-full rounded-md border-gray-300 text-sm">
                                            <input type="number" step="0.01" min="0" name="materials_new[{{ $i }}][quantity]" placeholder="Quantity" class="w-full rounded-md border-gray-300 text-sm">
                                            <input type="text" name="materials_new[{{ $i }}][unit]" placeholder="Unit (pcs, m, l)" class="w-full rounded-md border-gray-300 text-sm">
                                            <input type="number" step="0.01" min="0" name="materials_new[{{ $i }}][cost]" placeholder="Cost" class="w-full rounded-md border-gray-300 text-sm">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Labor Logs -->
                        <div>
                            <h4 class="mb-3 text-base font-semibold text-gray-900">Labor Logs</h4>
                            <div class="rounded-md border p-3 space-y-3">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500">Started At</label>
                                        <input type="datetime-local" name="labor_log[started_at]" class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500">Ended At</label>
                                        <input type="datetime-local" name="labor_log[ended_at]" class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500">Hours</label>
                                        <input type="number" step="0.01" min="0" name="labor_log[hours]" placeholder="Total Hours" class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">Notes</label>
                                    <textarea name="labor_log[notes]" rows="2" placeholder="What work was done?" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                                Close Ticket
                            </button>
                        </div>
                    </form>
                </section>
            @elseif($isReadOnly)
                <section class="rounded-xl bg-white p-4 shadow-sm sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Resolution Details</h3>

                    <div class="mt-6 space-y-6">
                        <!-- Resolution Code -->
                        <div>
                            <p class="text-gray-500 text-sm">Resolution Code</p>
                            <p class="font-medium text-gray-900">
                                {{ $workOrder->resolutionCode?->code ? $workOrder->resolutionCode->code . ' - ' . $workOrder->resolutionCode->label : '-' }}
                            </p>
                        </div>

                        <!-- Pictures -->
                        @if($workOrder->incident?->media->count())
                            <div>
                                <h4 class="mb-3 text-base font-semibold text-gray-900">Pictures / Videos</h4>
                                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                                    @foreach($workOrder->incident->media as $media)
                                        @if($media->media_type === 'image')
                                            <a href="{{ Storage::url($media->file_url) }}" target="_blank" class="block overflow-hidden rounded-lg border hover:opacity-90 transition">
                                                <img src="{{ Storage::url($media->file_url) }}" alt="Resolution photo" class="h-40 w-full object-cover">
                                            </a>
                                        @elseif($media->media_type === 'video')
                                            <video controls class="h-40 w-full rounded-lg border object-cover">
                                                <source src="{{ Storage::url($media->file_url) }}" type="video/mp4">
                                                Your browser does not support video.
                                            </video>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Materials -->
                        @if($workOrder->materials->count())
                            <div>
                                <h4 class="mb-3 text-base font-semibold text-gray-900">Materials Used</h4>
                                <div class="space-y-2">
                                    @foreach($workOrder->materials as $material)
                                        <div class="rounded-md border p-3 text-sm">
                                            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                                <div>
                                                    <p class="text-gray-500">Item</p>
                                                    <p class="font-medium text-gray-900">{{ $material->item_name }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Qty</p>
                                                    <p class="font-medium text-gray-900">{{ $material->quantity }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Unit</p>
                                                    <p class="font-medium text-gray-900">{{ $material->unit ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Cost</p>
                                                    <p class="font-medium text-gray-900">${{ $material->cost }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Labor Logs -->
                        @if($workOrder->laborLogs->count())
                            <div>
                                <h4 class="mb-3 text-base font-semibold text-gray-900">Labor Logs</h4>
                                <div class="space-y-3">
                                    @foreach($workOrder->laborLogs as $log)
                                        <div class="rounded-md border p-3 text-sm">
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                                <div>
                                                    <p class="text-gray-500">Started At</p>
                                                    <p class="font-medium text-gray-900">{{ $log->started_at?->format('Y-m-d H:i') ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Ended At</p>
                                                    <p class="font-medium text-gray-900">{{ $log->ended_at?->format('Y-m-d H:i') ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">Hours</p>
                                                    <p class="font-medium text-gray-900">{{ $log->hours ?? '-' }}</p>
                                                </div>
                                            </div>
                                            @if($log->notes)
                                                <div class="mt-2">
                                                    <p class="text-gray-500">Notes</p>
                                                    <p class="font-medium text-gray-900 whitespace-pre-wrap">{{ $log->notes }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif


        </div>
    </div>
</x-app-layout>
