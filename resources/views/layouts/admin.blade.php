<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} &middot; {{ __('Admin') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800{{ app()->getLocale() === 'hi' ? '|noto-sans-devanagari:400,500,600,700,800' : '' }}&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @include('layouts.pwa-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-800 bg-stone-100">
        @php
            $unreadContacts = \App\Models\ContactMessage::whereNull('read_at')->count();
            $pendingVets = \App\Models\Vet::where('is_active', false)->count();
            $pendingCaretakers = \App\Models\Caretaker::where('is_active', false)->count();
            $menu = [
                __('Overview') => [
                    ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => __('Dashboard'), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ],
                __('Marketplace') => [
                    ['route' => 'admin.animals.index', 'pattern' => 'admin.animals.*', 'label' => __('All Animals'), 'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                    ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => __('Animal Types'), 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                    ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => __('Orders'), 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ],
                __('Modules') => [
                    ['route' => 'admin.breeding.index', 'pattern' => 'admin.breeding.*', 'label' => __('Breeding'), 'icon' => 'M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z'],
                    ['route' => 'admin.vets.index', 'pattern' => 'admin.vets.*', 'label' => __('Animal Doctors'), 'icon' => 'M9 12h6m-3-3v6m9-3a9 9 0 11-18 0 9 9 0 0118 0z', 'badge' => $pendingVets],
                    ['route' => 'admin.caretakers.index', 'pattern' => 'admin.caretakers.*', 'label' => __('Caretakers'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'badge' => $pendingCaretakers],
                ],
                __('People') => [
                    ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'label' => __('Users & Roles'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'admin.sellers.create', 'pattern' => 'admin.sellers.*', 'label' => __('Add Seller'), 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                ],
                __('Communication') => [
                    ['route' => 'messages.index', 'pattern' => 'messages.*', 'label' => __('Chats'), 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                    ['route' => 'admin.contact-messages.index', 'pattern' => 'admin.contact-messages.*', 'label' => __('Contact Inbox'), 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'badge' => $unreadContacts],
                ],
            ];
        @endphp

        <div x-data="{ sidebar: false }" class="min-h-screen flex">
            <div x-show="sidebar" @click="sidebar = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden" style="display: none;"></div>

            <div class="lg:w-64 lg:shrink-0 lg:bg-stone-950">
            <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed lg:sticky top-0 z-40 h-screen w-64 bg-stone-950 text-stone-400 flex flex-col transition-transform lg:translate-x-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-6 h-16 border-b border-white/5">
                    <span class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center">
                        <x-application-logo class="h-5 w-5 fill-current text-white" />
                    </span>
                    <div>
                        <p class="font-extrabold text-white leading-tight">{{ config('app.name') }}</p>
                        <p class="text-[11px] text-stone-500 leading-tight">{{ __('Admin Panel') }}</p>
                    </div>
                </a>

                <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
                    @foreach ($menu as $group => $links)
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-stone-600">{{ $group }}</p>
                            <div class="space-y-1">
                                @foreach ($links as $link)
                                    @php $active = request()->routeIs($link['pattern']); @endphp
                                    <a href="{{ route($link['route']) }}"
                                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold transition {{ $active ? 'bg-amber-600 text-white shadow-lg shadow-amber-900/30' : 'hover:bg-white/5 hover:text-white' }}">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/></svg>
                                        <span class="flex-1">{{ $link['label'] }}</span>
                                        @if (! empty($link['badge']))
                                            <span class="px-1.5 min-w-[20px] text-center rounded-full text-[11px] font-bold {{ $active ? 'bg-white text-amber-700' : 'bg-amber-600 text-white' }}">{{ $link['badge'] }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="p-3 border-t border-white/5 space-y-1">
                    <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/5 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        {{ __('View website') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-white/5 hover:text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            {{ __('Log out') }}
                        </button>
                    </form>
                </div>
            </aside>
            </div>

            <div class="flex-1 min-w-0 flex flex-col">
                <header class="h-16 bg-white border-b border-stone-200 flex items-center gap-4 px-4 sm:px-8 sticky top-0 z-20">
                    <button @click="sidebar = true" class="lg:hidden p-2 -ml-2 rounded-lg text-stone-500 hover:bg-stone-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <x-back-button :fallback="isset($back) ? (string) $back : null" class="-ml-2" />
                    <div class="flex-1 min-w-0 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:text-stone-900 [&_h2]:truncate">
                        {{ $header ?? '' }}
                    </div>
                    <x-language-switcher />
                    <div class="flex items-center gap-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-stone-900 leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-stone-500 leading-tight">{{ __('Administrator') }}</p>
                        </div>
                        <span class="w-9 h-9 rounded-full bg-stone-900 text-white font-bold flex items-center justify-center">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-8 space-y-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
