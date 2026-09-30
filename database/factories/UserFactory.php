<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+91 9'.fake()->unique()->numerify('####').' '.fake()->numerify('#####'),
            'state' => 'Delhi',
            'city' => 'Delhi',
            'country' => 'India',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => false,
            'is_seller' => false,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
        ]);
    }

    public function breeder(): static
    {
        return $this->state(fn (array $attributes) => ['is_breeder' => true]);
    }

    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => ['is_doctor' => true]);
    }

    public function caretaker(): static
    {
        return $this->state(fn (array $attributes) => ['is_caretaker' => true]);
    }

    /** Places the person in a city from the India list (their profile location). */
    public function inCity(string $city, ?string $state = null): static
    {
        $place = \App\Support\Locations::findByCity($city, $state);

        return $this->state(fn () => ['state' => $place['state'], 'city' => $place['city'], 'latitude' => $place['lat'], 'longitude' => $place['lng']]);
    }

    public function seller(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_seller' => true,
        ]);
    }
}
