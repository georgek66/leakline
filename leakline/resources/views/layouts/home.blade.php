<!-- HOME LAYOUT -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">
    <title>{{ config('app.name', 'LeakLine') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col">

<!-- Navigation -->
<header class="bg-white border-b shadow-sm">
    <nav class="max-w-7xl mx-auto flex justify-between items-center py-4 px-6">
        <div class="text-xl font-bold">
            <a href="{{ url('/') }}">LeakLine</a>
        </div>
        <div class="flex items-center gap-6 text-sm">
            <a href="{{ route('citizen.report.create') }}" class="hover:underline">
                {{ __('nav.report') }}
            </a>

            <a href="{{ route('citizen.track.form') }}" class="hover:underline">
                {{ __('nav.track') }}
            </a>

            <a href="{{ route('login') }}" class="hover:underline">
                {{ __('nav.staff') }}
            </a>

            <a href="{{ route('citizen.lang', 'en') }}" class="hover:underline">
                {{ __('nav.lang_en') }}
            </a>

            <span class="text-gray-400 mx-1">|</span>

            <a href="{{ route('citizen.lang', 'el') }}" class="hover:underline">
                {{ __('nav.lang_el') }}
            </a>


        </div>
    </nav>
</header>

<!-- Page content will go here -->
<main class="w-full">
    <div class="w-full">
        @yield('content')
    </div>
</main>



<!-- Footer -->
<footer class="bg-gray-50 border-t py-4">
    <div class="max-w-7xl mx-auto text-center text-sm text-gray-600">
        <p>&copy; {{ date('Y') }} LeakLine. All rights reserved.</p>
        <div class="mt-2 space-x-4">
            <a href="{{ route('citizen.report.create') }}" class="hover:underline">
                Report a Leak
            </a>
            <a href="{{ route('citizen.track.form') }}" class="hover:underline">
                Track a Report
            </a>
            <a href="{{ route('login') }}" class="hover:underline">
                Staff Login
            </a>
        </div>
    </div>
</footer>
@stack('scripts')
</body>
</html>
