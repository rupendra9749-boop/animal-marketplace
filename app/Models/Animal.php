<?php

namespace App\Models;

use App\Models\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Animal extends Model
{
    /** @use HasFactory<\Database\Factories\AnimalFactory> */
    use HasFactory, HasLocation;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'slug',
        'breed',
        'age',
        'gender',
        'color',
        'weight',
        'is_vaccinated',
        'location',
        'state',
        'latitude',
        'longitude',
        'description',
        'price',
        'stock',
        'image_path',
        'is_active',
        'listing_type',
        'breeding_fee',
    ];

    public const TYPE_SALE = 'sale';

    public const TYPE_BREEDING = 'breeding';

    public const TYPE_BOTH = 'both';

    public const LISTING_TYPES = [self::TYPE_SALE, self::TYPE_BREEDING, self::TYPE_BOTH];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'breeding_fee' => 'decimal:2',
            'is_active' => 'boolean',
            'is_vaccinated' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /** Animals a buyer can purchase (normal listings and "sale + breeding"). */
    public function scopeForSale(Builder $query): Builder
    {
        return $query->whereIn('listing_type', [self::TYPE_SALE, self::TYPE_BOTH]);
    }

    /** Animals offered for breeding (breeding-only listings and "sale + breeding"). */
    public function scopeForBreeding(Builder $query): Builder
    {
        return $query->whereIn('listing_type', [self::TYPE_BREEDING, self::TYPE_BOTH]);
    }

    public function isForSale(): bool
    {
        return $this->listing_type !== self::TYPE_BREEDING;
    }

    public function offersBreeding(): bool
    {
        return in_array($this->listing_type, [self::TYPE_BREEDING, self::TYPE_BOTH], true);
    }

    public function isBreedingOnly(): bool
    {
        return $this->listing_type === self::TYPE_BREEDING;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return asset('storage/'.$this->image_path);
        }

        $slug = $this->category ? \Illuminate\Support\Str::slug($this->category->name) : 'default';

        if (file_exists(public_path("images/animals/{$slug}.svg"))) {
            return asset("images/animals/{$slug}.svg");
        }

        return asset('images/animals/default.svg');
    }

}
