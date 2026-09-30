<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Confirms the order to the buyer and gives them each seller's contact details. */
class OrderPlacedForBuyer extends Mailable
{
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Your order #:id is placed', ['id' => $this->order->id]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-placed-buyer', with: ['sellers' => $this->order->items->groupBy('seller_id')]);
    }
}
