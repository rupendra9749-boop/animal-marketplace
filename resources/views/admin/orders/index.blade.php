<x-admin-layout>
    <x-slot name="header">
        <h2>{{ __('Orders') }}</h2>
    </x-slot>

    <x-flash />

    <x-admin.intro :title="__('Orders')" :subtitle="$orders->total().' '.__('orders placed by buyers. Open one to update its status.')" />

    <div class="bg-white rounded-2xl border border-stone-200/70 overflow-x-auto">
        <table class="w-full text-sm text-left min-w-[640px]">
            <thead class="bg-stone-50 text-stone-500 uppercase text-xs">
                <tr>
                    <th class="px-5 py-3">{{ __('Order') }}</th>
                    <th class="px-5 py-3">{{ __('Buyer') }}</th>
                    <th class="px-5 py-3">{{ __('Date') }}</th>
                    <th class="px-5 py-3">{{ __('Status') }}</th>
                    <th class="px-5 py-3">{{ __('Total') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-5 py-3.5 font-bold text-stone-900">#{{ $order->id }}</td>
                        <td class="px-5 py-3.5 text-stone-700">{{ $order->buyer->name }}</td>
                        <td class="px-5 py-3.5 text-stone-500">{{ $order->created_at->translatedFormat('M d, Y') }}</td>
                        <td class="px-5 py-3.5"><x-order-status-badge :status="$order->status" /></td>
                        <td class="px-5 py-3.5 font-bold text-stone-900">{{ inr($order->total, 2) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-block px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 font-semibold">{{ __('View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-14 text-center">
                            <p class="text-4xl">🛍️</p>
                            <p class="mt-3 font-bold text-stone-900">{{ __('No orders yet') }}</p>
                            <p class="text-sm text-stone-500 mt-1">{{ __('Orders appear here as soon as a buyer checks out.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</x-admin-layout>
