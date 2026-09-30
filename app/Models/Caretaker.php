<?php

namespace App\Models;

use App\Models\Concerns\HasContactCard;
use App\Models\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Someone who looks after other people's animals: daily feeding and care, boarding, farm help, grooming... */
class Caretaker extends Model
{
    /** @use HasFactory<\Database\Factories\CaretakerFactory> */
    use HasContactCard, HasFactory, HasLocation;

    public const RATE_UNITS = ['day' => 'per day', 'hour' => 'per hour', 'month' => 'per month', 'visit' => 'per visit'];

    protected $fillable = [
        'user_id', 'name', 'slug', 'headline', 'services', 'about', 'experience_years', 'rate', 'rate_unit',
        'phone', 'whatsapp', 'email', 'address', 'state', 'city', 'latitude', 'longitude',
        'boarding', 'home_visit', 'available', 'photo_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'boarding' => 'boolean',
            'home_visit' => 'boolean',
            'available' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'rate' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** "₹500 per day", or null when the person has not set a rate. */
    public function rateLabel(): ?string
    {
        if ($this->rate === null || (float) $this->rate <= 0) {
            return null;
        }

        return inr($this->rate, 0).' '.__(self::RATE_UNITS[$this->rate_unit] ?? 'per day');
    }

    /**
     * How complete the profile is, so a caretaker knows what to add: [percent, list of missing things].
     *
     * @return array{0: int, 1: list<string>}
     */
    public function completeness(): array
    {
        $checks = [
            __('Headline') => filled($this->headline),
            __('Services') => filled($this->services),
            __('About') => filled($this->about),
            __('Years of experience') => $this->experience_years !== null,
            __('Rate') => $this->rate !== null,
            __('Address') => filled($this->address),
            __('Photo') => filled($this->photo_path),
            __('Animals you look after') => $this->exists && $this->categories->isNotEmpty(),
        ];

        $missing = array_keys(array_filter($checks, fn ($done) => ! $done));

        return [(int) round((count($checks) - count($missing)) / count($checks) * 100), $missing];
    }
}
