<x-panel-layout>
    <x-slot name="header"><h2>{{ __('My Orders') }}</h2></x-slot>

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
        @forelse ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-4 px-5 sm:px-6 py-4 hover:bg-stone-50 border-b border-stone-100 last:border-0">
                <span class="hidden sm:flex w-11 h-11 rounded-xl bg-stone-100 items-center justify-center text-xl">📦</span>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-stone-900">{{ __('Order') }} #{{ $order->id }}</p>
                    <p class="text-xs text-stone-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
                <x-order-status-badge :status="$order->status" />
                <span class="font-extrabold text-stone-900 w-20 text-right">{{ inr($order->total, 0) }}</span>
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <p class="text-5xl">🛍️</p>
                <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('No orders yet') }}</h3>
                <p class="text-sm text-stone-500 mt-1">{{ __('When you buy an animal, it will appear here.') }}</p>
                <a href="{{ route('home') }}" class="inline-block mt-6"><x-primary-button type="button">{{ __('Browse animals') }}</x-primary-button></a>
            </div>
        @endforelse
    </div>

    {{ $orders->links() }}
</x-panel-layout>
