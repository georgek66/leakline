@extends('layouts.home')
@section('title', 'Track your Report - LeakLine')
@section('content')
    <div class="max-w-3xl mx-auto p-4 sm:p-6">
        <div class="bg-white border rounded-2xl p-4 sm:p-6 shadow-sm">

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

                <button class="mt-4 w-full sm:w-auto px-4 py-2 rounded-lg border">
                    {{ __('citizen.track_button') }}
                </button>
            </form>

            @if(request()->isMethod('post'))
                <div class="mt-6 border-t pt-6">
                    @if($incident)
                        <div class="rounded-xl border bg-gray-50 p-4 space-y-2">
                            {{-- Contact Info --}}
                            @if($incident->contact && ($incident->contact->name || $incident->contact->phone || $incident->contact->email))
                                <div class="space-y-2">
                                    <p class="font-semibold">{{__('citizen.contact_info')}}:</p>

                                    @if($incident->contact->name)
                                        <p><strong>{{ __('citizen.name') }}:</strong> {{ e($incident->contact->name) }}</p>
                                    @endif

                                    @if($incident->contact->phone)
                                        <p><strong>{{__('citizen.phone')}}:</strong> {{ e($incident->contact->phone) }}</p>
                                    @endif

                                    @if($incident->contact->email)
                                        <p><strong>{{__('citizen.email')}}:</strong> {{ e($incident->contact->email) }}</p>
                                    @endif
                                </div>
                            {{--Delete contact info with gdpr token--}}
                                <form method="POST"
                                      action="{{ route('citizen.contact.delete', $incident->contact->gdpr_token) }}"
                                      class="mt-3">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="inline-flex items-center px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700"
                                            onclick="return confirm('This will permanently remove your contact info from this report. Continue?')">
                                        Delete my contact info
                                    </button>
                                </form>

                                <div class="my-4 border-t"></div>
                            @endif

                            <p>
                                <strong>{{ __('citizen.category') }}:</strong>
                                {{ optional($incident->category)->name }}
                            </p>
                            <p>
                                <strong>{{ __('citizen.severity') }}:</strong>
                                {{ ucfirst(optional($incident->severity)->name) }}
                            </p>
                            <p>
                                <strong>{{ __('citizen.status') }}:</strong>
                                {{ ucfirst($incident->status) }}
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ __('citizen.reported_at') }}:
                                {{ $incident->created_at->format('d M Y, H:i') }}
                            </p>
                            @if($incident -> description)
                                <p>
                                    <strong>{{ __('citizen.description') }}:</strong>
                                    {{ $incident->description }}
                                </p>
                            @endif
                            @if($incident->location)
                                <p>
                                    <strong>{{ __('citizen.location') }}:</strong>
                                    {{ $incident->location }}
                                </p>
                            @endif

                            <div class="my-4 border-t pt-4">
                                <p class="font-semibold mb-3">{{ __('citizen.media') }}:</p>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @forelse($incident->media as $media)
                                        @if($media->media_type === 'image' && $media->file_url)
                                            <div class="rounded-lg overflow-hidden border">
                                                <img
                                                    src="{{ asset('storage/' . $media->file_url) }}"
                                                    alt="Incident media"
                                                    class="w-full h-40 object-cover"
                                                >
                                            </div>
                                        @elseif($media->media_type === 'video' && $media->file_url)
                                            <div class="rounded-lg overflow-hidden border bg-black">
                                                <video class="w-full h-40" controls>
                                                    <source src="{{ asset('storage/' . $media->file_url) }}">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        @endif
                                    @empty
                                        <p class="text-sm text-gray-500 col-span-2 sm:col-span-3">
                                            {{ __('citizen.no_media_attached') }}
                                        </p>
                                    @endforelse
                                </div>
                            </div>

                        @else
                        <p class="text-red-600">
                            {{ __('citizen.track_not_found') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
