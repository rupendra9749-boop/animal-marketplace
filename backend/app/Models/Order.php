<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'animal_id', 'buyer_id', 'seller_id', 'animal_price', 'commission_amount',
        'total_amount', 'payment_status', 'payment_id', 'order_status', 'delivery_status',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
