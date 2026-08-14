<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'reviewer_id', 'reviewee_id', 'rating', 'comment'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }
}
