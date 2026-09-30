<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800{{ app()->getLocale() === 'hi' ? '|noto-sans-devanagari:400,500,600,700,800' : '' }}&display=swap" rel="stylesheet" />

        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @include('layouts.pwa-head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-stone-800 bg-stone-50">
        @php
            $user = auth()->user();
            $areas = ['seller', 'breeder', 'doctor', 'caretaker'];
            $panel = 'buyer';
            foreach ($areas as $area) {
                if (request()->routeIs($area.'.*')) {
                    // A seller's own panel holds breeding, doctor and caretaker too, so they all use one sidebar.
                    $panel = $user->isSeller() ? 'seller' : $area;
                    break;
                }
            }
            if ($panel === 'buyer' && ! request()->routeIs('account.*', 'orders.*', 'wishlist.*', 'profile.*')) {
                $panel = session('panel', 'buyer');
            }
            if (! array_key_exists($panel, $user->panels())) {
                $panel = 'buyer';
            }
            $panelNames = ['seller' => __('Seller Panel'), 'breeder' => __('Breeder Panel'), 'doctor' => __('Doctor Panel'), 'caretaker' => __('Caretaker Panel'), 'buyer' => __('My Account')];
            $unread = $user->unreadMessagesCount();
            $pendingApprovals = $user->isSeller() ? \App\Models\OrderItem::where('seller_id', $user->id)->where('status', 'pending')->distinct('order_id')->count('order_id') : 0;
            $cartCount = array_sum(session('cart', []));

            $icon = [
                'home' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'bag' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                'heart' => 'M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z',
                'compare' => 'M8 7h12M8 7l4-4M8 7l4 4M16 17H4m12 0l-4-4m4 4l-4 4',
                'chat' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'list' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
                'plus' => 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'cash' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'store' => 'M3 9l1-5h16l1 5M3 9v11h18V9M3 9h18M9 20v-6h6v6',
                'swap' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4',
                'profile' => 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ];

            $chats = [__('Communication'), [['messages.index', ['messages.*'], __('Chats'), 'chat', $unread]]];

            $menu = match ($panel) {
                'seller' => [
                    [__('Selling'), [
                        ['seller.dashboard', ['seller.dashboard'], __('Dashboard'), 'home'],
                        ['seller.sales.index', ['seller.sales.*'], __('Orders'), 'cash', $pendingApprovals],
                        ['seller.animals.index', ['seller.animals.index', 'seller.animals.show', 'seller.animals.edit'], __('My Animals'), 'list'],
                        ['seller.animals.create', ['seller.animals.create'], __('Add Animal'), 'plus'],
                    ]],
                    [__('Breeding'), [
                        ['breeder.animals.index', ['breeder.dashboard', 'breeder.animals.index', 'breeder.animals.show', 'breeder.animals.edit'], __('Breeding animals'), 'heart'],
                        ['breeder.animals.create', ['breeder.animals.create'], __('Add breeding animal'), 'plus'],
                    ]],
                    [__('My services'), [
                        ['doctor.profile', ['doctor.*'], __('Doctor profile'), 'profile'],
                        ['caretaker.profile', ['caretaker.*'], __('Caretaker profile'), 'profile'],
                    ]],
                    $chats,
                ],
                'breeder' => [
                    [__('Breeding'), [
                        ['breeder.dashboard', ['breeder.dashboard'], __('Dashboard'), 'home'],
                        ['breeder.animals.index', ['breeder.animals.index', 'breeder.animals.show', 'breeder.animals.edit'], __('My breeding animals'), 'list'],
                        ['breeder.animals.create', ['breeder.animals.create'], __('Add breeding animal'), 'plus'],
                    ]],
                    $chats,
                ],
                'doctor' => [
                    [__('My practice'), [
                        ['doctor.dashboard', ['doctor.dashboard'], __('Dashboard'), 'home'],
                        ['doctor.profile', ['doctor.profile*'], __('Clinic profile'), 'profile'],
                    ]],
                    $chats,
                ],
                'caretaker' => [
                    [__('My services'), [
                        ['caretaker.dashboard', ['caretaker.dashboard'], __('Dashboard'), 'home'],
                        ['caretaker.profile', ['caretaker.profile*'], __('Service profile'), 'profile'],
                    ]],
                    $chats,
                ],
                default => [
                    [__('My Account'), [
                        ['account.dashboard', ['account.*'], __('Overview'), 'home'],
                        ['orders.index', ['orders.*'], __('My Orders'), 'bag'],
                        ['wishlist.index', ['wishlist.*'], __('Wishlist'), 'heart'],
                        ['compare.index', ['compare.*'], __('Compare'), 'compare'],
                        ['messages.index', ['messages.*'], __('Messages'), 'chat', $unread],
                        ['profile.edit', ['profile.*'], __('Profile'), 'user'],
                    ]],
                ],
            };

            // Everything else this person can open (a seller who also wants to shop, a doctor who is also a caretaker...).
            $homes = ['admin' => 'admin.dashboard', 'seller' => 'seller.dashboard', 'breeder' => 'breeder.dashboard', 'doctor' => 'doctor.dashboard', 'caretaker' => 'caretaker.dashboard', 'buyer' => 'account.dashboard'];
            $switch = [];
            foreach ($user->panels() as $key => $label) {
                if ($key !== $panel) {
                    $switch[] = [$homes[$key], [], $key === 'buyer' ? __('My buyer account') : $label, $key === 'buyer' ? 'swap' : 'store'];
                }
            }
            if ($switch) {
                $menu[] = [__('Switch'), $switch];
            }
        @endphp

        <div x-data="{ sidebar: false }" class="min-h-screen flex">
            <div x-show="sidebar" @click="sidebar = false" class="fixed inset-0 bg-black/40 z-30 lg:hidden" style="display: none;"></div>

            <div class="lg:w-64 lg:shrink-0 lg:bg-white lg:border-r lg:border-stone-200">
                <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
                       class="fixed lg:sticky top-0 z-40 h-screen w-64 bg-white border-r border-stone-200 lg:border-r-0 flex flex-col transition-transform lg:translate-x-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 px-5 h-16 border-b border-stone-100">
                        <span class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center shadow-sm">
                            <x-application-logo class="h-5 w-5 fill-current text-white" />
                        </span>
                        <div>
                            <p class="font-extrabold text-stone-900 leading-tight">{{ config('app.name') }}</p>
                            <p class="text-[11px] font-semibold text-amber-700 leading-tight">{{ $panelNames[$panel] }}</p>
                        </div>
                    </a>

                    <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-6">
                        @foreach ($menu as [$group, $links])
                            <div>
                                <p class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-stone-400">{{ $group }}</p>
                                <div class="space-y-1">
                                    @foreach ($links as $link)
                                        @php
                                            [$route, $patterns, $label, $ic] = $link;
                                            $badge = $link[4] ?? 0;
                                            $active = $patterns && request()->routeIs(...$patterns);
                                        @endphp
                                        <a href="{{ route($route) }}"
                                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition {{ $active ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-200' : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' }}">
                                            <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-amber-600' : 'text-stone-400' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon[$ic] }}"/></svg>
                                            <span class="flex-1">{{ $label }}</span>
                                            @if ($badge)
                                                <span class="px-1.5 min-w-[20px] text-center rounded-full bg-amber-600 text-white text-[11px] font-bold">{{ $badge }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    <div class="p-3 border-t border-stone-100">
                        <div class="flex items-center gap-3 px-2 py-2">
                            <span class="w-9 h-9 rounded-full bg-stone-900 text-white font-bold flex items-center justify-center shrink-0">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-stone-900 truncate">{{ $user->name }}</p>
                                <p class="text-xs text-stone-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 w-full text-left px-3 py-2 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-100">{{ __('Log out') }}</button>
                        </form>
                    </div>
                </aside>
            </div>

            <div class="flex-1 min-w-0 flex flex-col">
                <header class="h-16 bg-white/95 backdrop-blur border-b border-stone-200 flex items-center gap-3 px-4 sm:px-8 sticky top-0 z-20">
                    <button @click="sidebar = true" class="lg:hidden p-2 -ml-2 rounded-lg text-stone-500 hover:bg-stone-100" aria-label="{{ __('Menu') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <x-back-button :fallback="isset($back) ? (string) $back : null" class="-ml-2" />
                    <div class="flex-1 min-w-0 [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:text-stone-900 [&_h2]:truncate">
                        {{ $header ?? '' }}
                    </div>
                    <x-language-switcher />
                    <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-stone-600 hover:bg-stone-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['store'] }}"/></svg>
                        {{ __('Browse animals') }}
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-lg text-stone-600 hover:bg-stone-100" title="{{ __('Cart') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if ($cartCount)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                </header>

                <x-profile-nudge />

                <main class="flex-1 p-4 sm:p-8 space-y-6 max-w-6xl w-full">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
