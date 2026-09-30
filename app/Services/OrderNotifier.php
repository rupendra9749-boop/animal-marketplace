<?php

namespace App\Services;

use App\Mail\NewOrderForSeller;
use App\Mail\OrderDecision;
use App\Mail\OrderPlacedForBuyer;
use App\Models\Order;
use App\Models\User;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The emails around an order. A mail problem (a full mailbox, a host that blocks mail) is written to the log
 * and never stops the order or the seller's decision itself.
 */
class OrderNotifier
{
    // Each person's email is written in their own language (->locale(null) keeps the site default).

    /** The order was just placed: the buyer gets a receipt with seller details, each seller gets a request. */
    public function placed(Order $order): void
    {
        $order->load(['buyer', 'items.seller']);

        $this->send(fn () => Mail::to($order->buyer->email)->locale($order->buyer->locale)->send(new OrderPlacedForBuyer($order)));

        foreach ($order->items->groupBy('seller_id') as $items) {
            $seller = $items->first()->seller;
            $this->send(fn () => Mail::to($seller->email)->locale($seller->locale)->send(new NewOrderForSeller($order, $seller, $items)));
        }
    }

    /**
     * A seller approved or declined their part of the order: tell the buyer.
     *
     * @param  Collection<int, \App\Models\OrderItem>  $items
     */
    public function decided(Order $order, User $seller, Collection $items, string $decision): void
    {
        $order->loadMissing('buyer');

        $this->send(fn () => Mail::to($order->buyer->email)->locale($order->buyer->locale)->send(new OrderDecision($order, $seller, $items, $decision)));
    }

    private function send(Closure $send): void
    {
        try {
            $send();
        } catch (Throwable $e) {
            report($e);
        }
    }
}
