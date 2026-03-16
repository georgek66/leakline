@extends('layouts.home')
@section('title', 'Home - LeakLine')

@section('content')
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-cyan-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <p class="inline-flex items-center rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-semibold tracking-wide uppercase">
                        {{ __('citizen.home_badge') }}
                    </p>

                    <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                        {{ __('citizen.home_title') }}
                    </h1>

                    <p class="mt-5 text-base sm:text-lg text-gray-600 max-w-xl">
                        {{ __('citizen.home_subtitle') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('citizen.report.create') }}"
                           class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                            {{ __('citizen.home_cta_report') }}
                        </a>

                        <a href="{{ route('citizen.track.form') }}"
                           class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-gray-300 bg-white text-gray-800 font-medium hover:bg-gray-50 transition">
                            {{ __('citizen.home_cta_track') }}
                        </a>
                    </div>
                    <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                        <p class="font-semibold">{{ __('citizen.install_info_title') }}</p>
                        <p class="mt-2">
                            {{ __('citizen.install_info_subtitle') }}
                        </p>
                        <ul class="mt-2 space-y-1 list-disc list-inside">
                            <li>{{ __('citizen.install_info_android') }}</li>
                            <li>{{ __('citizen.install_info_ios') }}</li>
                            <li>{{ __('citizen.install_info_desktop') }}</li>
                        </ul>
                    </div>

                </div>

                <div class="relative">
                    <div class="rounded-2xl border border-blue-100 bg-white/90 backdrop-blur shadow-xl p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('citizen.home_how_it_works') }}</h2>
                        <ol class="mt-4 space-y-4 text-sm text-gray-600">
                            <li class="flex gap-3">
                                <span class="mt-0.5 h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold inline-flex items-center justify-center">1</span>
                                <span>{{ __('citizen.home_step_1') }}</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold inline-flex items-center justify-center">2</span>
                                <span>{{ __('citizen.home_step_2') }}</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 h-6 w-6 rounded-full bg-blue-600 text-white text-xs font-bold inline-flex items-center justify-center">3</span>
                                <span>{{ __('citizen.home_step_3') }}</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
