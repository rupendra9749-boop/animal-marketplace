<x-mail-frame>
    @if ($approved)
        <p style="margin:0 0 12px;font-size:18px;"><strong>&#9989; {{ __('Good news, :name!', ['name' => $order->buyer->name]) }}</strong></p>
        <p style="margin:0 0 8px;">{{ __(':seller approved your order #:id.', ['seller' => $seller->name, 'id' => $order->id]) }}</p>
    @else
        <p style="margin:0 0 12px;font-size:18px;"><strong>{{ __('Hello :name,', ['name' => $order->buyer->name]) }}</strong></p>
        <p style="margin:0 0 8px;">{{ __(':seller could not accept these animals from your order #:id. The animals are back on sale and nothing is charged.', ['seller' => $seller->name, 'id' => $order->id]) }}</p>
    @endif

    <x-mail-items :items="$items" />

    @if ($approved)
        <x-mail-contact :user="$seller" :label="__('Seller details')" />
        <p style="margin:0;">{{ __('Please contact the seller to agree when and where to meet.') }}</p>
    @endif

    <x-mail-button :href="route('orders.show', $order)">{{ __('See your order') }}</x-mail-button>
</x-mail-frame>
