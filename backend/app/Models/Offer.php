<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'animal_id', 'buyer_id', 'seller_id', 'offered_price', 'status', 'counter_price',
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
}
