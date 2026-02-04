@extends('layouts.home')

@section('content')
    <div class="max-w-xl mx-auto p-6">
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <h1 class="text-2xl font-bold">You are offline</h1>
            <p class="mt-2 text-gray-600">
                The app is not connected to the internet. Please reconnect and try again.
            </p>
        </div>
    </div>
@endsection
