<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Order') }} #{{ $order->id }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('Order').' #'.$order->id" :subtitle="__('Placed on :date. Each seller approves their own animals; you can decide for a seller or change the order status.', ['date' => $order->created_at->translatedFormat('d M Y, H:i')])">
        <x-slot name="actions">
            <a href="{{ route('admin.orders.index') }}"><x-secondary-button type="button">← {{ __('All orders') }}</x-secondary-button></a>
        </x-slot>
    </x-admin.intro>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Buyer') }}</p>
            <p class="mt-1 font-bold text-stone-900">{{ $order->buyer->name }}</p>
            <p class="text-sm text-stone-600">{{ $order->buyer->email }}</p>
            @if ($order->buyer->phone)<p class="text-sm text-stone-600">{{ $order->buyer->phone }}</p>@endif
            @if ($order->buyer->city)<p class="text-sm text-stone-500">{{ __($order->buyer->city) }}, {{ __($order->buyer->state) }}</p>@endif
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Deliver to') }}</p>
            <p class="mt-1 font-bold text-stone-900">{{ $order->shipping_name }}</p>
            <p class="text-sm text-stone-600 whitespace-pre-line">{{ $order->shipping_address }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200/70 p-5">
            <p class="text-xs uppercase tracking-wider font-semibold text-stone-400">{{ __('Total') }}</p>
            <p class="mt-1 text-2xl font-extrabold text-stone-900">{{ inr($order->total, 2) }}</p>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-3">
                @csrf
                @method('PATCH')
                <label for="status" class="block text-xs font-semibold text-stone-500">{{ __('Order status') }}</label>
                <select id="status" name="status" class="mt-1 block w-full border-stone-300 rounded-xl text-sm focus:border-amber-500 focus:ring-amber-500" onchange="this.form.submit()">
                    @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ \App\Models\Order::statusLabel($status) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    @foreach ($order->items->groupBy('seller_id') as $sellerId => $items)
        @php $seller = $items->first()->seller; $waiting = $items->where('status', 'pending'); @endphp
        <div class="bg-white rounded-2xl border border-stone-200/70 overflow-hidden">
            <div class="flex flex-wrap items-center gap-3 px-5 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/60">
                <div class="min-w-0">
                    <p class="font-bold text-stone-900">{{ $seller?->name ?? __('Deleted seller') }}</p>
                    @if ($seller)<p class="text-xs text-stone-500">{{ collect([$seller->phone, $seller->email, __($seller->city)])->filter()->join(' · ') }}</p>@endif
                </div>
                @if ($waiting->isNotEmpty() && $seller)
                    <div class="ml-auto flex items-center gap-2">
                        <form method="POST" action="{{ route('seller.sales.decline', ['order' => $order, 'seller_id' => $sellerId]) }}" onsubmit="return confirm('{{ __('Decline for this seller? The animals go back on sale.') }}')">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg border border-red-200 text-red-700 text-sm font-bold hover:bg-red-50">{{ __('Decline') }}</button>
                        </form>
                        <form method="POST" action="{{ route('seller.sales.approve', ['order' => $order, 'seller_id' => $sellerId]) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700">✓ {{ __('Approve') }}</button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="divide-y divide-stone-100">
                @foreach ($items as $item)
                    <div class="flex items-center gap-4 px-5 sm:px-6 py-3.5">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-stone-900">{{ $item->animal_name }} <span class="text-stone-400">× {{ $item->quantity }}</span></p>
                            <p class="text-xs text-stone-500">{{ inr($item->price, 2) }} {{ __('each') }}</p>
                        </div>
                        <x-order-status-badge :status="$item->status" :item="true" />
                        <p class="w-24 text-right font-bold text-stone-900">{{ inr($item->lineTotal(), 2) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</x-admin-layout>
