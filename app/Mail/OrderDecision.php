<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

/** Tells the buyer that a seller approved or declined their part of an order. */
class OrderDecision extends Mailable
{
    /** @param  Collection<int, OrderItem>  $items  the seller's items that were decided */
    public function __construct(public Order $order, public User $seller, public Collection $items, public string $decision) {}

    public function envelope(): Envelope
    {
        $approved = $this->decision === OrderItem::APPROVED;

        return new Envelope(subject: $approved
            ? __('Order #:id: :seller approved your order', ['id' => $this->order->id, 'seller' => $this->seller->name])
            : __('Order #:id: :seller could not accept your order', ['id' => $this->order->id, 'seller' => $this->seller->name]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-decision', with: ['approved' => $this->decision === OrderItem::APPROVED]);
    }
}
