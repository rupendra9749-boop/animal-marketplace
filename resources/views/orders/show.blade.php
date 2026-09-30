<x-panel-layout>
    <x-slot name="back">{{ route('orders.index') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <h2>{{ __('Order') }} #{{ $order->id }}</h2>
        </div>
    </x-slot>

    <x-flash />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Status') }}</p>
            <div class="mt-2"><x-order-status-badge :status="$order->status" /></div>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Placed on') }}</p>
            <p class="mt-2 font-bold text-stone-900">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Total') }}</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-900">{{ inr($order->total, 2) }}</p>
        </div>
    </div>

    @if ($order->status === 'pending')
        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 text-sm text-amber-900">
            ⏳ {{ __('Each seller has been emailed and will approve your order. You get an email when they do. You can already call or message them below to agree when and where to meet.') }}
        </div>
    @endif

    {{-- One block per seller: what they sell you, whether they approved, and how to reach them --}}
    @foreach ($order->items->groupBy('seller_id') as $items)
        @php $seller = $items->first()->seller; @endphp
        <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-5">
                <div class="lg:col-span-3 divide-y divide-stone-100">
                    <h3 class="font-bold text-stone-900 px-6 pt-5 pb-3">{{ __('From :seller', ['seller' => $seller?->name ?? __('a seller')]) }}</h3>
                    @foreach ($items as $item)
                        <div class="flex items-center gap-4 px-6 py-4">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-stone-900">{{ $item->animal_name }}</p>
                                <p class="text-xs text-stone-500">{{ inr($item->price, 2) }} × {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-stone-900">{{ inr($item->lineTotal(), 2) }}</p>
                                <x-order-status-badge :status="$item->status" :item="true" class="!text-[10px] !px-2 !py-0.5" />
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($seller)
                    <div class="lg:col-span-2 px-6 py-5 lg:border-l border-t lg:border-t-0 border-stone-100 bg-stone-50/50">
                        <p class="text-xs font-bold uppercase tracking-wider text-stone-400">{{ __('Seller details') }}</p>
                        <p class="mt-1 font-extrabold text-stone-900">{{ $seller->name }}</p>
                        <div class="mt-2 space-y-1 text-sm text-stone-600">
                            @if ($seller->city)<p>📍 {{ __($seller->city) }}, {{ __($seller->state) }}</p>@endif
                            @if ($seller->phone)<p>📞 <a href="tel:{{ preg_replace('/[^0-9+]/', '', $seller->phone) }}" class="font-semibold text-emerald-700 hover:underline">{{ $seller->phone }}</a></p>@endif
                            <p>✉️ <a href="mailto:{{ $seller->email }}" class="hover:underline break-all">{{ $seller->email }}</a></p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($seller->phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $seller->phone) }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">📞 {{ __('Call') }}</a>
                                <a href="https://wa.me/{{ \App\Support\Phone::whatsapp($seller->phone) }}" target="_blank" rel="noopener" class="px-4 py-2 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm font-bold hover:bg-green-100">💬 {{ __('WhatsApp') }}</a>
                            @endif
                            @if ($seller->id !== auth()->id())
                                <form method="POST" action="{{ route('messages.provider', $seller) }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-lg border border-stone-200 bg-white text-stone-700 text-sm font-bold hover:border-amber-500">✉️ {{ __('Message') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    <div class="bg-white rounded-2xl border border-stone-200/70 p-6">
        <h3 class="font-bold text-stone-900">{{ __('Delivery details') }}</h3>
        <p class="mt-3 font-semibold text-stone-800">{{ $order->shipping_name }}</p>
        <p class="text-stone-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
    </div>
</x-panel-layout>
