<x-mail-frame>
    <p style="margin:0 0 12px;font-size:18px;"><strong>{{ __('Thank you, :name!', ['name' => $order->buyer->name]) }}</strong></p>
    <p style="margin:0 0 8px;">{{ __('Your order #:id is placed. Each seller will look at it and approve it - you get another email when they do.', ['id' => $order->id]) }}</p>

    @foreach ($sellers as $items)
        @php $seller = $items->first()->seller; @endphp
        <p style="margin:18px 0 0;"><strong>{{ __('From :seller', ['seller' => $seller->name]) }}</strong></p>
        <x-mail-items :items="$items" />
        <x-mail-contact :user="$seller" :label="__('Seller details')" />
    @endforeach

    <p style="margin:6px 0 0;">{{ __('You can call or message the seller to agree when and where to meet.') }}</p>
    <x-mail-button :href="route('orders.show', $order)">{{ __('See your order') }}</x-mail-button>
</x-mail-frame>
