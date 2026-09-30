<?php

namespace App\Models;

use App\Models\Concerns\HasContactCard;
use App\Models\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vet extends Model
{
    /** @use HasFactory<\Database\Factories\VetFactory> */
    use HasContactCard, HasFactory, HasLocation;

    protected $fillable = [
        'user_id', 'name', 'slug', 'clinic_name', 'qualification', 'experience_years', 'services', 'about',
        'phone', 'whatsapp', 'email', 'address', 'state', 'city', 'latitude', 'longitude', 'consultation_fee',
        'timings', 'home_visit', 'emergency', 'photo_path', 'is_active', 'source', 'source_url', 'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'home_visit' => 'boolean',
            'emergency' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'consultation_fee' => 'decimal:2',
        ];
    }

    /** Found by the AI web search rather than added by an admin or by the doctor. */
    public function isAiFound(): bool
    {
        return $this->source === 'ai';
    }

    /** The website a doctor found by AI was listed on, e.g. "justdial.com". */
    public function sourceHost(): ?string
    {
        $host = $this->source_url ? parse_url($this->source_url, PHP_URL_HOST) : null;

        return $host ? preg_replace('/^www\./', '', $host) : null;
    }

    /** @return BelongsTo<User, $this> */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * How complete the profile is, so a doctor knows what to add: [percent, list of missing things].
     *
     * @return array{0: int, 1: list<string>}
     */
    public function completeness(): array
    {
        $checks = [
            __('Clinic name') => filled($this->clinic_name),
            __('Qualification') => filled($this->qualification),
            __('Years of experience') => $this->experience_years !== null,
            __('Services') => filled($this->services),
            __('About') => filled($this->about),
            __('Address') => filled($this->address),
            __('Timings') => filled($this->timings),
            __('Consultation fee') => $this->consultation_fee !== null,
            __('Photo') => filled($this->photo_path),
            __('Animals treated') => $this->exists && $this->categories->isNotEmpty(),
        ];

        $missing = array_keys(array_filter($checks, fn ($done) => ! $done));

        return [(int) round((count($checks) - count($missing)) / count($checks) * 100), $missing];
    }
}
