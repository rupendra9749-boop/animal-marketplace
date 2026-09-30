<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    /**
     * @return HasMany<Animal, $this>
     */
    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    /**
     * @return BelongsToMany<Vet, $this>
     */
    public function vets(): BelongsToMany
    {
        return $this->belongsToMany(Vet::class);
    }

    /**
     * @return BelongsToMany<Caretaker, $this>
     */
    public function caretakers(): BelongsToMany
    {
        return $this->belongsToMany(Caretaker::class);
    }
}
