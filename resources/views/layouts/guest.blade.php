<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800{{ app()->getLocale() === 'hi' ? '|noto-sans-devanagari:400,500,600,700,800' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @include('layouts.pwa-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-stone-900 antialiased">
        <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">
            <div class="hidden lg:flex relative bg-stone-950 overflow-hidden flex-col justify-between p-12">
                <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 30% 30%, #d97706 0, transparent 45%), radial-gradient(circle at 80% 80%, #92400e 0, transparent 40%);"></div>
                <a href="{{ route('home') }}" class="relative flex items-center gap-2">
                    <span class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center">
                        <x-application-logo class="h-6 w-6 fill-current text-white" />
                    </span>
                    <span class="font-extrabold text-xl text-white">{{ config('app.name') }}</span>
                </a>
                <div class="relative">
                    <div class="grid grid-cols-3 gap-3 max-w-md">
                        @foreach (['cow', 'dog', 'goat', 'bird', 'buffalo', 'cat'] as $slug)
                            <img src="{{ asset("images/animals/{$slug}.svg") }}" alt="" class="rounded-2xl ring-1 ring-white/10">
                        @endforeach
                    </div>
                    <h2 class="mt-10 text-3xl font-extrabold text-white leading-tight">{{ __('Buy & sell healthy animals, the simple way.') }}</h2>
                    <p class="mt-3 text-stone-400 max-w-md">{{ __('Compare listings, chat with sellers, and find animals within 250 km of you.') }}</p>
                </div>
                <p class="relative text-xs text-stone-500">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </div>

            <div class="relative flex flex-col justify-center items-center px-6 py-12 bg-stone-50">
                <x-back-button class="absolute top-3 left-4 lg:hidden" />
                <x-language-switcher class="absolute top-4 right-4" />
                <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2 mb-8">
                    <span class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center">
                        <x-application-logo class="h-6 w-6 fill-current text-white" />
                    </span>
                    <span class="font-extrabold text-xl text-stone-900">{{ config('app.name') }}</span>
                </a>

                <div class="w-full max-w-md bg-white rounded-2xl border border-stone-200/70 shadow-xl shadow-stone-200/50 p-8">
                    {{ $slot }}
                </div>

                <a href="{{ route('home') }}" class="mt-6 text-sm text-stone-500 hover:text-stone-800">{{ __('← Back to home') }}</a>
            </div>
        </div>
    </body>
</html>
