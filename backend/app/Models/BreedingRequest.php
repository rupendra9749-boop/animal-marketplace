<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreedingRequest extends Model
{
    public $timestamps = false;
    protected $fillable = ['breeding_profile_id', 'requested_by', 'status', 'message'];

    public function breedingProfile()
    {
        return $this->belongsTo(BreedingProfile::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
