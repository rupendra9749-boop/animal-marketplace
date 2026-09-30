<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'email', 'phone', 'password', 'state', 'city', 'country', 'latitude', 'longitude', 'is_seller', 'is_breeder', 'is_doctor', 'is_caretaker', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Deleting an account removes everything of theirs that is public: their doctor and caretaker profiles (which show a
     * phone number) and all uploaded photos. Listings, wishlist, chats and orders go with the row (database cascade).
     */
    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            $photos = $user->animals()->pluck('image_path')
                ->merge([$user->vet?->photo_path, $user->caretaker?->photo_path])
                ->filter()->all();

            $user->vet?->delete();
            $user->caretaker?->delete();

            if ($photos) {
                Storage::disk('public')->delete($photos);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_seller' => 'boolean',
            'is_breeder' => 'boolean',
            'is_doctor' => 'boolean',
            'is_caretaker' => 'boolean',
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Who can do what:
     *  - everyone can browse, buy and take services (doctor, caretaker, breeding);
     *  - a seller can also sell animals, list breeding animals and offer a doctor and a caretaker profile;
     *  - an admin can do everything a seller can, plus manage the whole site.
     * (The older separate breeder / doctor / caretaker flags still work for people who only have one of them.)
     */
    public function isSeller(): bool
    {
        return (bool) ($this->is_admin || $this->is_seller);
    }

    public function isBreeder(): bool
    {
        return $this->isSeller() || (bool) $this->is_breeder;
    }

    public function isDoctor(): bool
    {
        return $this->isSeller() || (bool) $this->is_doctor;
    }

    public function isCaretaker(): bool
    {
        return $this->isSeller() || (bool) $this->is_caretaker;
    }

    /** True once the person has told us their phone and home city. */
    public function hasContactDetails(): bool
    {
        return filled($this->phone) && filled($this->city) && $this->latitude !== null;
    }

    /** Sets the home city (and its map position) from the India location list. */
    public function setHomeCity(?string $state, ?string $city): void
    {
        $hit = \App\Support\Locations::find($state, $city);

        $this->state = $hit['state'] ?? null;
        $this->city = $hit['city'] ?? null;
        $this->country = \App\Support\Locations::COUNTRY;
        $this->latitude = $hit['lat'] ?? null;
        $this->longitude = $hit['lng'] ?? null;
    }

    /**
     * The areas this person can open, in the order they are offered: [key => label].
     *
     * @return array<string, string>
     */
    public function panels(): array
    {
        return array_filter([
            'admin' => $this->isAdmin() ? __('Admin panel') : null,
            'seller' => $this->isSeller() ? __('Seller panel') : null,
            // A seller's panel already holds breeding, doctor and caretaker; these are for people with only one of them.
            'breeder' => ! $this->isSeller() && $this->is_breeder ? __('Breeder panel') : null,
            'doctor' => ! $this->isSeller() && $this->is_doctor ? __('Doctor panel') : null,
            'caretaker' => ! $this->isSeller() && $this->is_caretaker ? __('Caretaker panel') : null,
            'buyer' => __('My account'),
        ]);
    }

    /** The home page of the person's main area (used after login and for the /dashboard link). */
    public function homeRoute(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isSeller() => 'seller.dashboard',
            $this->is_breeder => 'breeder.dashboard',
            $this->is_doctor => 'doctor.dashboard',
            $this->is_caretaker => 'caretaker.dashboard',
            default => 'account.dashboard',
        };
    }

    /** @return HasOne<Vet, $this> the clinic profile of a person with the doctor role */
    public function vet(): HasOne
    {
        return $this->hasOne(Vet::class);
    }

    /** @return HasOne<Caretaker, $this> the service profile of a person with the caretaker role */
    public function caretaker(): HasOne
    {
        return $this->hasOne(Caretaker::class);
    }

    public function unreadMessagesCount(): int
    {
        return Message::whereNull('read_at')
            ->where('sender_id', '!=', $this->id)
            ->whereHas('conversation', fn ($q) => $q->where(
                fn ($q) => $q->where('buyer_id', $this->id)->orWhere('seller_id', $this->id)
            ))
            ->count();
    }

    /**
     * @return HasMany<Animal, $this>
     */
    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return BelongsToMany<Animal, $this>
     */
    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Animal::class, 'wishlists')->withTimestamps();
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function sellerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }
}
