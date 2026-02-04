{{-- resources/views/citizen/home.blade.php --}}
@extends('layouts.home')

@section('content')
    <h2 class="text-2xl font-bold mb-2">Report a water leak</h2>
    <p class="mb-4 text-gray-600">Citizen PWA will live here.</p>
    <a href="{{ route('citizen.report.create') }}"
       class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Open report page
    </a>
@endsection
