{{-- Advertisement landing page (see PromoController). --}}
@php
    $lang = app()->getLocale() === 'hi' ? 'hi' : 'en';
    $shareUrl = route($theme === 'marketplace' ? 'promo' : 'promo.theme', $theme === 'marketplace' ? [] : ['theme' => $theme]);
    $shareImage = asset("images/social/{$theme}-{$lang}.png");
    $shareText = $page['title'].' — '.$page['sub'];
    $title = e($page['title']);
    $headline = str_contains($title, e($page['highlight']))
        ? str_replace(e($page['highlight']), '<span class="text-amber-400">'.e($page['highlight']).'</span>', $title)
        : $title;
    $themeLinks = [
        'marketplace' => ['🐄', __('Buy & sell animals')],
        'doctor' => ['🩺', __('Animal doctors')],
        'sell' => ['💰', __('Sell for free')],
        'breeding' => ['🧬', __('Breeding & care')],
    ];
@endphp

<x-app-layout>
    <x-slot name="title">{{ $page['title'] }}</x-slot>
    <x-slot name="head">
        <meta name="description" content="{{ $page['sub'] }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="AnimalMandi">
        <meta property="og:title" content="{{ $page['title'] }}">
        <meta property="og:description" content="{{ $page['sub'] }}">
        <meta property="og:url" content="{{ $shareUrl }}">
        <meta property="og:image" content="{{ $shareImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:locale" content="{{ $lang === 'hi' ? 'hi_IN' : 'en_IN' }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $shareImage }}">
    </x-slot>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-stone-950 text-white">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 88% 10%, #d97706 0, transparent 45%), radial-gradient(circle at 0% 100%, #92400e 0, transparent 40%);"></div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block px-4 py-1.5 rounded-full border border-amber-400/40 bg-black/20 text-amber-300 text-sm font-bold">{{ $page['badge'] }}</span>
                <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold leading-tight tracking-tight">{!! $headline !!}</h1>
                <p class="mt-5 text-lg sm:text-xl text-stone-300">{{ $page['sub'] }}</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ $page['cta'][1] }}" class="text-center px-7 py-4 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white text-lg font-extrabold shadow-lg shadow-amber-900/40">{{ $page['cta'][0] }} →</a>
                    <a href="{{ url('/download/') }}" class="text-center px-7 py-4 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/20 text-white text-lg font-bold">📱 {{ __('Get the Android app') }}</a>
                </div>
                <p class="mt-4 text-sm text-stone-400">{{ __('Free · English and हिन्दी · All of India') }}</p>
            </div>
            <div class="grid {{ count($page['animals']) === 6 ? 'grid-cols-3' : 'grid-cols-2' }} gap-4 max-w-md w-full mx-auto">
                @foreach ($page['animals'] as $animal)
                    <img src="{{ asset("images/animals/{$animal}.svg") }}" alt="" class="w-full aspect-square rounded-3xl shadow-2xl shadow-black/40 ring-1 ring-white/10">
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach ($page['features'] as [$icon, $heading, $text])
                <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
                    <span class="text-3xl">{{ $icon }}</span>
                    <h2 class="mt-3 text-lg font-extrabold text-stone-900">{{ $heading }}</h2>
                    <p class="mt-1 text-stone-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-amber-50 border-y border-amber-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-stone-900 text-center">{{ __('How it works') }}</h2>
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ([[1, __('Sign up free'), __('Your name, mobile number and city. It takes one minute.')], [2, __('Choose your city'), __('See animals, doctors and caretakers near you.')], [3, __('Call or order'), __('Talk to the seller or doctor directly — no middlemen.')]] as [$n, $heading, $text])
                    <div class="flex gap-4 items-start">
                        <span class="shrink-0 w-11 h-11 rounded-full bg-amber-600 text-white font-extrabold text-lg flex items-center justify-center">{{ $n }}</span>
                        <div>
                            <h3 class="font-extrabold text-stone-900">{{ $heading }}</h3>
                            <p class="text-stone-600">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Share + other offers --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl border border-stone-200/70 p-6" x-data="{ copied: false }">
            <h2 class="text-xl font-extrabold text-stone-900">{{ __('Share with farmers and friends') }}</h2>
            <p class="mt-1 text-stone-600">{{ __('Send this page on WhatsApp or Facebook. The picture shows up with the link.') }}</p>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="https://wa.me/?text={{ rawurlencode($shareText.' '.$shareUrl) }}" target="_blank" rel="noopener" class="text-center px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">WhatsApp</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}" target="_blank" rel="noopener" class="text-center px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold">Facebook</a>
                <button type="button" @click="navigator.clipboard ? navigator.clipboard.writeText(@js($shareUrl)).then(() => copied = true) : window.prompt('', @js($shareUrl))" class="px-4 py-3 rounded-xl bg-stone-900 hover:bg-stone-800 text-white font-bold">
                    <span x-show="! copied">{{ __('Copy link') }}</span><span x-show="copied" style="display:none">{{ __('Copied!') }}</span>
                </button>
            </div>
            <p class="mt-3 text-sm text-stone-500 break-all">{{ $shareUrl }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
            <h2 class="text-xl font-extrabold text-stone-900">{{ __('More on AnimalMandi') }}</h2>
            <div class="mt-5 grid grid-cols-1 gap-3">
                @foreach ($others as $other)
                    <a href="{{ $other === 'marketplace' ? route('promo') : route('promo.theme', $other) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl border border-stone-200 hover:border-amber-400 hover:bg-amber-50 font-bold text-stone-800">
                        <span class="text-2xl">{{ $themeLinks[$other][0] }}</span> {{ $themeLinks[$other][1] }} <span class="ml-auto text-amber-700">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final call to action --}}
    <section class="px-4 sm:px-6 lg:px-8 pb-4">
        <div class="max-w-6xl mx-auto rounded-3xl bg-stone-900 text-white p-8 sm:p-12 text-center">
            <h2 class="text-2xl sm:text-4xl font-extrabold">{{ __('Join AnimalMandi today — it is free') }}</h2>
            <a href="{{ $page['cta'][1] }}" class="inline-block mt-6 px-8 py-4 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white text-lg font-extrabold">{{ $page['cta'][0] }} →</a>
        </div>
    </section>
</x-app-layout>
