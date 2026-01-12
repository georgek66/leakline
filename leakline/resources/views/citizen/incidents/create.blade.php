{{-- resources/views/citizen/report/create.blade.php --}}

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="max-w-6xl mx-auto p-4 sm:p-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold">Report a Leak</h1>
            <p class="text-sm text-gray-600 mt-1">Select category, severity, add details, then drop a pin on the map.</p>
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
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('citizen.report.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- LEFT: Form --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Category</label>
                        <select name="category_id" class="w-full rounded-xl border-gray-300 focus:border-black focus:ring-black">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Severity</label>
                        <select name="severity_id" class="w-full rounded-xl border-gray-300 focus:border-black focus:ring-black">
                            @foreach ($severities as $sev)
                                <option value="{{ $sev->id }}">{{ $sev->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea
                        name="description"
                        rows="4"
                        class="w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                        placeholder="Describe the leak..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Photos / Video (optional)</label>
                    <input type="file" name="media[]" multiple accept="image/*,video/*"
                           class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                    <p class="text-xs text-gray-500 mt-1">Up to 5 files (images or a short video).</p>
                </div>

                {{-- Hidden fields --}}
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <button
                    id="submitBtn"
                    type="submit"
                    disabled
                    class="w-full rounded-xl py-2.5 font-medium text-white bg-black disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90">
                    Submit
                </button>

                <p id="pinHint" class="text-sm text-red-600">
                    Please click the map to place a pin before submitting.
                </p>

                <p id="pinInfo" class="text-sm text-gray-600 hidden"></p>
            </div>

            {{-- RIGHT: Map --}}
            <div class="bg-white rounded-2xl shadow-sm border p-5">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-lg font-semibold">Location</h2>

                    <button type="button" id="useLocation" class="text-sm underline">
                        Use my location
                    </button>
                </div>

                <div id="map" class="w-full rounded-xl overflow-hidden border" style="height: 420px;"></div>

                <p class="text-xs text-gray-500 mt-3">
                    Tip: Zoom in for accuracy, then click exactly where the leak is.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
    const map = L.map('map').setView([35.1856, 33.3823], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const pinHint = document.getElementById('pinHint');
    const pinInfo = document.getElementById('pinInfo');
    const submitBtn = document.getElementById('submitBtn');
    const useLocationBtn = document.getElementById('useLocation');

    function setPin(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }

        latEl.value = lat;
        lngEl.value = lng;

        pinHint.classList.add('hidden');
        pinInfo.classList.remove('hidden');
        pinInfo.textContent = `Pin set at: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

        submitBtn.disabled = false;
    }

    map.on('click', function (e) {
        setPin(e.latlng.lat, e.latlng.lng);
    });

    useLocationBtn.addEventListener('click', function () {
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
