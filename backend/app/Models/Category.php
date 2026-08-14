<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'status'];

    public function breeds()
    {
        return $this->hasMany(Breed::class);
    }
}

