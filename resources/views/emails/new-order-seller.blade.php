<x-mail-frame>
    <p style="margin:0 0 12px;font-size:18px;"><strong>{{ __('Hello :name,', ['name' => $seller->name]) }}</strong></p>
    <p style="margin:0 0 8px;">{{ __(':buyer ordered from you (order #:id). Please look at it and approve or decline it.', ['buyer' => $buyer->name, 'id' => $order->id]) }}</p>

    <x-mail-items :items="$items" />

    <p style="margin:14px 0 0;"><strong>{{ __('The buyer') }}</strong></p>
    <x-mail-contact :user="$buyer" />

    <p style="margin:0;"><strong>{{ __('Delivery details') }}</strong><br>{{ $order->shipping_name }}<br>{!! nl2br(e($order->shipping_address)) !!}</p>

    <x-mail-button :href="route('seller.sales.index')">{{ __('Review and approve the order') }}</x-mail-button>
    <p style="margin:12px 0 0;font-size:13px;color:#78716c;">{{ __('When you approve, the buyer gets an email with your contact details so you can meet and hand over the animals.') }}</p>
</x-mail-frame>
