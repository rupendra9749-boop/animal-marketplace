<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Overview') }}</h2></x-slot>

    <div class="rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 text-white p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-amber-100 text-sm">{{ now()->translatedFormat('l, d F') }}</p>
            <h1 class="text-2xl font-extrabold mt-1">{{ __('Hello,') }} {{ auth()->user()->name }} 👋</h1>
            <p class="text-amber-100 text-sm mt-1">{{ __('Track your orders, saved animals and chats in one place.') }}</p>
        </div>
        <a href="{{ route('home') }}" class="shrink-0 bg-white text-amber-700 font-bold text-sm px-5 py-3 rounded-xl shadow hover:bg-amber-50 text-center">{{ __('Browse animals') }}</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            [__('Orders'), $stats['orders'], inr($stats['spent'], 0).' '.__('spent'), 'bg-sky-500', route('orders.index'), 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            [__('Wishlist'), $stats['wishlist'], __('saved animals'), 'bg-rose-500', route('wishlist.index'), 'M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z'],
            [__('In cart'), $stats['cart'], __('ready to buy'), 'bg-amber-500', route('cart.index'), 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
            [__('Messages'), $stats['unread'], __('unread'), 'bg-violet-500', route('messages.index'), 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ] as [$label, $value, $sub, $color, $href, $path])
            <a href="{{ $href }}" class="bg-white rounded-2xl border border-stone-200/70 p-5 hover:shadow-lg transition">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-500">{{ $label }}</p>
                    <span class="w-10 h-10 rounded-xl {{ $color }} text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-stone-900">{{ $value }}</p>
                <p class="text-xs text-stone-500 mt-1">{{ $sub }}</p>
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100">
            <h3 class="font-bold text-stone-900">{{ __('Recent orders') }}</h3>
            <a href="{{ route('orders.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">{{ __('View all') }}</a>
        </div>
        @forelse ($recentOrders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-stone-50 border-b border-stone-100 last:border-0">
                <span class="w-10 h-10 rounded-xl bg-stone-100 flex items-center justify-center text-lg">📦</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-stone-900">{{ __('Order') }} #{{ $order->id }}</p>
                    <p class="text-xs text-stone-500">{{ $order->created_at->translatedFormat('d M Y') }} · {{ $order->items_count }} {{ __('item(s)') }}</p>
                </div>
                <x-order-status-badge :status="$order->status" />
                <span class="font-bold text-stone-900 w-20 text-right">{{ inr($order->total, 0) }}</span>
            </a>
        @empty
            <div class="px-6 py-12 text-center">
                <p class="text-4xl">🐄</p>
                <p class="mt-3 font-semibold text-stone-700">{{ __('No orders yet') }}</p>
                <p class="text-sm text-stone-500">{{ __('Your purchases will show up here.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($saved->isNotEmpty())
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-stone-900">{{ __('From your wishlist') }}</h3>
                <a href="{{ route('wishlist.index') }}" class="text-sm font-semibold text-amber-700 hover:underline">{{ __('View all') }}</a>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($saved as $animal)
                    <x-animal-card :animal="$animal" :wishlisted="true" :compared="in_array($animal->id, session('compare', []))" />
                @endforeach
            </div>
        </div>
    @endif

    @unless (auth()->user()->isSeller())
        <div class="rounded-2xl bg-stone-900 text-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="font-bold text-lg">🌾 {{ __('Have animals to sell, or a service to offer?') }}</p>
                <p class="text-sm text-stone-400 mt-1">{{ __('Become a seller: sell animals, list breeding animals, and create a doctor and a caretaker profile. You can still buy and use services.') }}</p>
            </div>
            <form method="POST" action="{{ route('account.become-seller') }}" class="shrink-0">
                @csrf
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm px-5 py-3 rounded-xl text-center">{{ __('Become a seller') }}</button>
            </form>
        </div>
    @endunless
</x-panel-layout>
