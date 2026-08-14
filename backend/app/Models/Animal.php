<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'breed_id', 'gender', 'age_years', 'age_months',
        'weight', 'color', 'health_status', 'vaccination_cert', 'price',
        'is_negotiable', 'description', 'purpose', 'video', 'latitude',
        'longitude', 'address', 'status', 'views_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }

    public function images()
    {
        return $this->hasMany(AnimalImage::class);
    }
}
