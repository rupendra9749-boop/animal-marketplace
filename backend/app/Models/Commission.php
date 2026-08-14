<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'amount', 'status', 'settled_to_seller'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
