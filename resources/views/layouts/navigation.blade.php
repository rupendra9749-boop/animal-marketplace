@php
    $primaryLinks = [
        ['route' => 'home', 'pattern' => 'home', 'label' => __('Home')],
        ['route' => 'breeding.browse', 'pattern' => 'breeding.*', 'label' => __('Breeding')],
        ['route' => 'vets.index', 'pattern' => 'vets.*', 'label' => __('Vets')],
        ['route' => 'caretakers.index', 'pattern' => 'caretakers.*', 'label' => __('Caretakers')],
        ['route' => 'about', 'pattern' => 'about', 'label' => __('About')],
        ['route' => 'contact', 'pattern' => 'contact', 'label' => __('Contact')],
    ];
@endphp

@php $areaRoutes = ['admin' => 'admin.dashboard', 'seller' => 'seller.dashboard', 'breeder' => 'breeder.dashboard', 'doctor' => 'doctor.dashboard', 'caretaker' => 'caretaker.dashboard']; @endphp

<nav x-data="{ open: false }" class="bg-white/95 backdrop-blur border-b border-stone-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-10">
                <x-back-button :fallback="isset($back) ? (string) $back : null" class="xl:hidden -ml-2 -mr-4" />
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="w-9 h-9 rounded-xl bg-amber-600 flex items-center justify-center shadow-sm">
                        <x-application-logo class="h-5 w-5 fill-current text-white" />
                    </span>
                    {{-- On narrow phones the back arrow needs the room: inner pages show only the paw logo there. --}}
                    <span class="font-extrabold text-base sm:text-lg text-stone-900 tracking-tight {{ request()->routeIs('home', 'login', 'register', '*.dashboard') ? '' : 'hidden min-[400px]:inline' }}">{{ config('app.name') }}</span>
                </a>

                <div class="hidden xl:flex items-center gap-1">
                    @foreach ($primaryLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs($link['pattern']) ? 'text-amber-700 bg-amber-50' : 'text-stone-600 hover:text-stone-900 hover:bg-stone-100' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden xl:flex items-center gap-2">
                @include('layouts.location-chip')
                <x-language-switcher />
                <a href="{{ route('compare.index') }}" class="relative p-2 rounded-lg text-stone-600 hover:bg-stone-100 hover:text-stone-900" title="{{ __('Compare') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12M8 7l4-4M8 7l4 4M16 17H4m12 0l-4-4m4 4l-4 4"/></svg>
                    @if ($compareCount)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $compareCount }}</span>
                    @endif
                </a>

                @auth
                    <a href="{{ route('wishlist.index') }}" class="p-2 rounded-lg text-stone-600 hover:bg-stone-100 hover:text-stone-900" title="{{ __('Wishlist') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"/></svg>
                    </a>
                    <a href="{{ route('cart.index') }}" class="relative p-2 rounded-lg text-stone-600 hover:bg-stone-100 hover:text-stone-900" title="{{ __('Cart') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if ($cartCount)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <div class="w-px h-6 bg-stone-200 mx-2"></div>

                    <x-dropdown align="right" width="w-56">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-stone-100 transition">
                                <span class="w-8 h-8 rounded-full bg-stone-900 text-white text-sm font-bold flex items-center justify-center">
                                    {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="text-sm font-semibold text-stone-700 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-stone-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @php $areaRoutes = ['admin' => 'admin.dashboard', 'seller' => 'seller.dashboard', 'breeder' => 'breeder.dashboard', 'doctor' => 'doctor.dashboard', 'caretaker' => 'caretaker.dashboard']; @endphp
                            @foreach (auth()->user()->panels() as $key => $label)
                                @if ($key !== 'buyer')
                                    <x-dropdown-link :href="route($areaRoutes[$key])" class="font-semibold text-amber-700">{{ $label }}</x-dropdown-link>
                                @endif
                            @endforeach
                            <x-dropdown-link :href="route('account.dashboard')">{{ __('My Account') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('orders.index')">{{ __('My Orders') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('messages.index')">{{ __('Messages') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            @unless (auth()->user()->isAdmin())
                                <x-dropdown-link :href="route('support')">{{ __('Contact Support') }}</x-dropdown-link>
                            @endunless
                            <div class="border-t border-stone-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-semibold text-stone-700 hover:text-stone-900">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold shadow-sm transition">{{ __('Get Started') }}</a>
                @endauth
            </div>

            <div class="xl:hidden flex items-center gap-1">
                @guest
                    <a href="{{ route('login') }}" class="px-2 py-2 text-sm font-semibold text-stone-700 whitespace-nowrap">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="px-3 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold whitespace-nowrap">{{ __('Sign up') }}</a>
                @endguest
                @auth
                    <a href="{{ route('account.dashboard') }}" class="w-9 h-9 rounded-full bg-stone-900 text-white text-sm font-bold flex items-center justify-center" aria-label="{{ __('My Account') }}">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</a>
                @endauth
            <button @click="open = ! open" class="p-2 rounded-lg text-stone-500 hover:bg-stone-100" aria-label="{{ __('Menu') }}">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden xl:hidden border-t border-stone-100 bg-white">
        <div class="px-4 py-3 space-y-1">
            <div class="pb-2">@include('layouts.location-chip', ['inline' => true])</div>
            <div class="pb-2"><x-language-switcher /></div>
            @foreach ($primaryLinks as $link)
                <a href="{{ route($link['route']) }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs($link['pattern']) ? 'bg-amber-50 text-amber-700' : 'text-stone-700 hover:bg-stone-50' }}">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('compare.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('Compare') }} @if($compareCount)({{ $compareCount }})@endif</a>

            @auth
                <a href="{{ route('account.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('My Account') }}</a>
                <a href="{{ route('cart.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('Cart') }} @if($cartCount)({{ $cartCount }})@endif</a>
                <a href="{{ route('wishlist.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('Wishlist') }}</a>
                <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('My Orders') }}</a>
                <a href="{{ route('messages.index') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-700 hover:bg-stone-50">{{ __('Messages') }}</a>
                @foreach (auth()->user()->panels() as $key => $label)
                    @if ($key !== 'buyer')
                        <a href="{{ route($areaRoutes[$key] ?? 'account.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-amber-700 hover:bg-stone-50">{{ $label }}</a>
                    @endif
                @endforeach

                <div class="border-t border-stone-100 pt-3 mt-3">
                    <p class="px-3 text-sm font-semibold text-stone-900">{{ Auth::user()->name }}</p>
                    <p class="px-3 text-xs text-stone-500 mb-2">{{ Auth::user()->email }}</p>
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">{{ __('Profile') }}</a>
                    @unless (auth()->user()->isAdmin())
                        <a href="{{ route('support') }}" class="block px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">{{ __('Contact Support') }}</a>
                    @endunless
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">{{ __('Log Out') }}</button>
                    </form>
                </div>
            @else
                <div class="border-t border-stone-100 pt-3 mt-3 flex gap-2">
                    <a href="{{ route('login') }}" class="flex-1 text-center px-3 py-2 rounded-lg border border-stone-200 text-sm font-semibold">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="flex-1 text-center px-3 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold">{{ __('Get Started') }}</a>
                </div>
            @endauth
        </div>
    </div>
</nav>
