@props(['status', 'item' => false])

{{-- $item = false: the status of a whole order; true: the status of one seller's part of an order. --}}
@php
    $colors = [
        'pending' => 'bg-amber-100 text-amber-800',
        'processing' => 'bg-sky-100 text-sky-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-red-100 text-red-800',
        'declined' => 'bg-red-100 text-red-800',
    ];
    $label = $item
        ? ['pending' => __('Waiting for seller'), 'approved' => __('Approved'), 'declined' => __('Declined')][$status] ?? ucfirst($status)
        : \App\Models\Order::statusLabel($status);
@endphp

<span {{ $attributes->merge(['class' => 'px-2.5 py-1 rounded-full text-xs font-bold '.($colors[$status] ?? 'bg-stone-100 text-stone-700')]) }}>
    {{ $label }}
</span>
