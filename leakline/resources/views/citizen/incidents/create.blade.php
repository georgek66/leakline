{{-- create.blade.php view with map on the left and form on the right --}}
@extends('layouts.home')

@push('styles')
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"
    />
@endpush
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
@endpush
@section('content')
<div class="max-w-6xl mx-auto p-4 sm:p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold">{{ __('citizen.report_title') }}</h1>
            <p class="text-sm text-gray-600 mt-1">
                {{ __('citizen.report_help') }}
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    @continue(blank($error))
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="leakline_report" method="POST" action="{{ route('citizen.report.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Use a three column grid on large screens so the map can span two columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- LEFT: Map --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border p-5">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-lg font-semibold">{{ __('citizen.location') }}</h2>

                    <button type="button" id="useLocation" class="text-sm underline">
                        {{ __('citizen.use_my_location') }}
                    </button>
                </div>
                <!-- The map container. Increase the height a bit to give users more space -->
                <div id="map" class="w-full rounded-xl overflow-hidden border" style="height: 500px;"></div>

                <p class="text-xs text-gray-500 mt-3">
                    {{ __('citizen.map_tip') }}
                </p>

                <!-- Address box that will be filled when a pin is placed -->
                <div
                    id="addressBox"
                    class="hidden mt-3 rounded-xl border border-green-200 bg-green-50 px-4 py-2 text-green-800 text-sm"
                ></div>
            </div>

            {{-- RIGHT: Form --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5 space-y-5">

                {{-- Contact details --}}
                <div>
                    <h3 class="font-semibold text-sm mb-3">
                        {{ __('citizen.contact_details_optional') }}
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('citizen.name') }}</label>
                            <input
                                type="text"
                                name="contact_name"
                                value="{{ old('contact_name') }}"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('citizen.phone') }}</label>
                            <input
                                type="text"
                                name="contact_phone"
                                value="{{ old('contact_phone') }}"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('citizen.email') }}</label>
                            <input
                                type="email"
                                name="contact_email"
                                value="{{ old('contact_email') }}"
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                            >
                        </div>

                        <div class="flex items-start gap-2 mt-2">
                            <input
                                id="consent"
                                type="checkbox"
                                name="consent"
                                value="1"
                                class="mt-1 rounded border-gray-300"
                                {{ old('consent') ? 'checked' : '' }}
                            >
                            <label for="consent" class="text-sm text-gray-700">
                                {{ __('citizen.consent_text') }}
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('citizen.category') }}</label>
                    <select
                        name="category_id"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                    >
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Severity --}}
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('citizen.severity') }}</label>
                    <select
                        name="severity_id"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                    >
                        @foreach ($severities as $sev)
                            <option value="{{ $sev->id }}" @selected(old('severity_id') == $sev->id)>
                                {{ $sev->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('citizen.description') }}</label>
                    <textarea
                        name="description"
                        rows="4"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:ring-black"
                        placeholder="{{ __('citizen.describe_placeholder') }}"
                    >{{ old('description') }}</textarea>
                </div>

                {{-- Media --}}
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('citizen.media_optional') }}</label>
                    <input
                        type="file"
                        name="media[]"
                        multiple
                        accept="image/*,video/*"
                        class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
                    >
                    <p class="text-xs text-gray-500 mt-1">{{ __('citizen.media_help') }}</p>
                </div>

                {{-- Hidden fields --}}
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="location" id="location">

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700"
                >
                    {{ __('citizen.submit') }}
                </button>



                <p id="pinHint" class="hidden text-sm text-red-600 mt-2">
                    {{ __('citizen.pin_required') }}
                </p>



                <p id="pinInfo" class="text-sm text-gray-600 hidden"></p>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
    // Initialize the Leaflet map
    const map = L.map('map').setView([35.1856, 33.3823], 12); // Nicosia
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);



    // Add a geocoder control for searching addresses (optional)
    if (typeof L.Control.Geocoder !== 'undefined') {
        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
        })
            .on('markgeocode', function (e) {
                const latlng = e.geocode.center;
                map.setView(latlng, 16);
                setPin(latlng.lat, latlng.lng);
            })
            .addTo(map);
    }

    let marker = null;
    const form = document.getElementById('leakline_report');
    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const pinHint = document.getElementById('pinHint');
    const pinInfo = document.getElementById('pinInfo');
    const useLocationBtn = document.getElementById('useLocation');
    const addressBox = document.getElementById('addressBox');



    // Helper function to reverse geocode latitude/longitude into an address
    async function reverseGeocode(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;

        try {
            const res = await fetch(url, {
                headers: { Accept: 'application/json' },
            });

            if (!res.ok) return null;

            const data = await res.json();
            const a = data.address || {};

            const street = a.road || a.pedestrian || a.footway || '';
            const number = a.house_number || '';
            const postcode = a.postcode || '';
            const city = a.city || a.town || a.village || '';

            const addressParts = [
                `${street} ${number}`.trim(),
                postcode,
                city
            ].filter(Boolean);

            return addressParts.join(', ');
        } catch (err) {
            console.error(err);
            return null;
        }
    }


    function setPin(lat, lng) {
        // If a marker already exists, just move it
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        // Update hidden fields
        latEl.value = lat;
        lngEl.value = lng;
        pinHint.classList.add('hidden');


        // Reverse geocode and display address
        reverseGeocode(lat, lng).then((address) => {
            if (address) {
                addressBox.textContent = `Address: ${address}`;
                addressBox.classList.remove('hidden');
                document.getElementById('location').value = address;
            } else {
                addressBox.textContent = 'Address not found';
                addressBox.classList.remove('hidden');
            }
        });
    }
    form.addEventListener('submit',function (e){
        if(!latEl || !lngEl){
            e.preventDefault();
            pinHint.classList.remove('hidden');
        }
    })

    // When user clicks on the map, place the marker and fetch the address
    map.on('click', function (e) {
        setPin(e.latlng.lat, e.latlng.lng);
    });

    // Use browser geolocation to place a pin at the user's current location
    useLocationBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 16);
                setPin(lat, lng);
            },
            () => alert('Could not get your location. Please drop a pin on the map.'),
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });
</script>
@endpush
