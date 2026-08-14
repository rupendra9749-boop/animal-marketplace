<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreedingProfile extends Model
{
    protected $fillable = [
        'user_id', 'animal_id', 'breed_id', 'gender_required', 'age_range',
        'pedigree_details', 'location', 'latitude', 'longitude', 'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function requests()
    {
        return $this->hasMany(BreedingRequest::class);
    }
}
