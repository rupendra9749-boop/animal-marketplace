<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? trim(strip_tags($title)).' · ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800{{ app()->getLocale() === 'hi' ? '|noto-sans-devanagari:400,500,600,700,800' : '' }}&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @include('layouts.pwa-head')
        @isset($head)
            {{ $head }}
        @else
            <meta property="og:site_name" content="AnimalMandi">
            <meta property="og:title" content="{{ isset($title) ? trim(strip_tags($title)).' · ' : '' }}AnimalMandi">
            <meta property="og:description" content="{{ __('Buy & sell healthy animals near you') }} — {{ __('Cows, buffaloes, goats, dogs, birds & more — directly from sellers in your area.') }}">
            <meta property="og:image" content="{{ asset('images/social/marketplace-'.(app()->getLocale() === 'hi' ? 'hi' : 'en').'.png') }}">
            <meta name="twitter:card" content="summary_large_image">
        @endisset
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-800">
        <div class="min-h-screen bg-stone-50 flex flex-col">
            @include('layouts.navigation')
            <x-profile-nudge />

            @isset($header)
                <header class="bg-white border-b border-stone-200">
                    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:text-stone-900">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            @include('layouts.footer')
        </div>

        @php $compareCount = count(session('compare', [])); @endphp
        @if ($compareCount && ! request()->routeIs('compare.*'))
            <div class="fixed bottom-4 inset-x-0 z-40 px-4">
                <div class="max-w-xl mx-auto bg-stone-900 text-white rounded-2xl shadow-2xl px-5 py-3 flex items-center justify-between gap-4">
                    <p class="text-sm">
                        <span class="font-bold text-amber-400">{{ $compareCount }}</span>
                        {{ $compareCount === 1 ? __('animal selected — add one more to compare') : __('animals ready to compare') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('compare.clear') }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-stone-400 hover:text-white px-2">{{ __('Clear') }}</button>
                        </form>
                        <a href="{{ route('compare.index') }}" class="bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold px-4 py-2 rounded-xl">{{ __('Compare') }}</a>
                    </div>
                </div>
            </div>
        @endif
    </body>
</html>
