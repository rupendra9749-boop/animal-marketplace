<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

/** Tells a seller that a buyer ordered their animals and asks them to approve or decline. */
class NewOrderForSeller extends Mailable
{
    /** @param  Collection<int, \App\Models\OrderItem>  $items  only this seller's part of the order */
    public function __construct(public Order $order, public User $seller, public Collection $items) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('New order #:id - please approve it', ['id' => $this->order->id]));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-order-seller', with: ['buyer' => $this->order->buyer]);
    }
}
