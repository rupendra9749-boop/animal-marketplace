<?php

namespace Database\Factories;

use App\Models\Caretaker;
use App\Support\Locations;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Caretaker>
 */
class CaretakerFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $city = Locations::findByCity(collect(['Delhi', 'Mumbai', 'Ludhiana', 'Jaipur', 'Lucknow', 'Pune', 'Chennai'])->random());
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'headline' => 'Experienced animal caretaker',
            'services' => 'Daily feeding, Grooming',
            'experience_years' => fake()->numberBetween(1, 20),
            'rate' => 500,
            'rate_unit' => 'day',
            'phone' => '+91 00000 '.fake()->numerify('#####'),
            'address' => fake()->streetAddress(),
            'state' => $city['state'],
            'city' => $city['city'],
            'latitude' => $city['lat'],
            'longitude' => $city['lng'],
            'boarding' => false,
            'home_visit' => true,
            'available' => true,
            'is_active' => true,
        ];
    }

    public function inCity(string $name, ?string $state = null): static
    {
        $city = Locations::findByCity($name, $state);

        return $this->state(fn () => ['city' => $city['city'], 'state' => $city['state'], 'latitude' => $city['lat'], 'longitude' => $city['lng']]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
