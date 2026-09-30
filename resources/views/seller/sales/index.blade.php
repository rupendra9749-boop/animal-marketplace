<x-panel-layout>
    <x-slot name="header"><h2>{{ __('Orders') }}</h2></x-slot>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            [__('Waiting for you'), $totals['waiting'], 'text-amber-700'],
            [__('Orders'), $totals['orders'], 'text-stone-900'],
            [__('Animals sold'), $totals['units'], 'text-stone-900'],
            [__('Revenue'), inr($totals['revenue'], 0), 'text-stone-900'],
        ] as [$label, $value, $tone])
            <div class="bg-white rounded-2xl border border-stone-200/70 p-4 sm:p-5">
                <p class="text-xs sm:text-sm font-semibold text-stone-500">{{ $label }}</p>
                <p class="mt-1 text-xl sm:text-3xl font-extrabold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    @forelse ($orders as $order)
        @php
            $waiting = $order->items->where('status', 'pending');
            $mine = $order->items->sum(fn ($i) => $i->lineTotal());
        @endphp
        <div class="bg-white rounded-2xl border {{ $waiting->isNotEmpty() ? 'border-amber-300 ring-1 ring-amber-200' : 'border-stone-200/70' }} overflow-hidden">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-5 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/60">
                <p class="font-extrabold text-stone-900">{{ __('Order') }} #{{ $order->id }}</p>
                <p class="text-sm text-stone-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                <span class="ml-auto"><x-order-status-badge :status="$waiting->isNotEmpty() ? 'pending' : ($order->items->contains('status', 'approved') ? 'approved' : 'declined')" :item="true" /></span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
                {{-- The animals --}}
                <div class="lg:col-span-3 divide-y divide-stone-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-4 px-5 sm:px-6 py-3.5">
                            <img src="{{ $item->animal?->imageUrl() ?? asset('images/animals/default.svg') }}" alt="" class="w-12 h-12 rounded-xl object-cover bg-stone-100 shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-stone-900 truncate">{{ $item->animal_name }} <span class="font-medium text-stone-400">× {{ $item->quantity }}</span></p>
                                <p class="text-xs text-stone-500">{{ inr($item->price, 2) }} {{ __('each') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-extrabold text-stone-900">{{ inr($item->lineTotal(), 0) }}</p>
                                <x-order-status-badge :status="$item->status" :item="true" class="!text-[10px] !px-2 !py-0.5" />
                            </div>
                        </div>
                    @endforeach
                    <div class="flex items-center justify-between px-5 sm:px-6 py-3 bg-stone-50/60">
                        <span class="text-sm font-semibold text-stone-500">{{ __('Your part of the order') }}</span>
                        <span class="font-extrabold text-stone-900">{{ inr($mine, 0) }}</span>
                    </div>
                </div>

                {{-- The buyer --}}
                <div class="lg:col-span-2 px-5 sm:px-6 py-4 lg:border-l border-t lg:border-t-0 border-stone-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-stone-400">{{ __('Buyer') }}</p>
                    <p class="mt-1 font-bold text-stone-900">{{ $order->buyer->name }}</p>
                    <div class="mt-1 space-y-0.5 text-sm text-stone-600">
                        @if ($order->buyer->phone)<p>📞 <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->buyer->phone) }}" class="font-semibold text-emerald-700 hover:underline">{{ $order->buyer->phone }}</a></p>@endif
                        <p>✉️ <a href="mailto:{{ $order->buyer->email }}" class="hover:underline break-all">{{ $order->buyer->email }}</a></p>
                        @if ($order->buyer->city)<p>📍 {{ __($order->buyer->city) }}, {{ __($order->buyer->state) }}</p>@endif
                    </div>
                    <p class="mt-3 text-xs font-bold uppercase tracking-wider text-stone-400">{{ __('Deliver to') }}</p>
                    <p class="mt-1 text-sm font-semibold text-stone-800">{{ $order->shipping_name }}</p>
                    <p class="text-sm text-stone-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
                </div>
            </div>

            @if ($waiting->isNotEmpty())
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 sm:px-6 py-4 border-t border-amber-200 bg-amber-50">
                    <p class="flex-1 text-sm font-semibold text-amber-900">{{ __('Please approve or decline this order. The buyer is emailed your decision.') }}</p>
                    <form method="POST" action="{{ route('seller.sales.decline', $order) }}" onsubmit="return confirm('{{ __('Decline this order? The animals go back on sale.') }}')">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-red-200 bg-white text-red-700 text-sm font-bold hover:bg-red-50">{{ __('Decline') }}</button>
                    </form>
                    <form method="POST" action="{{ route('seller.sales.approve', $order) }}">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700">✓ {{ __('Approve order') }}</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-stone-200/70 px-6 py-16 text-center">
            <p class="text-5xl">📈</p>
            <h3 class="mt-4 text-lg font-bold text-stone-900">{{ __('No orders yet') }}</h3>
            <p class="text-sm text-stone-500 mt-1">{{ __('When buyers order your animals, they show up here and you get an email.') }}</p>
        </div>
    @endforelse

    {{ $orders->links() }}
</x-panel-layout>
