<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use App\Support\Locations;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $breeds = [
            'Holstein Friesian', 'Jersey', 'Murrah', 'Sahiwal', 'Gir',
            'Labrador', 'German Shepherd', 'Beagle', 'Boer', 'Rhode Island Red',
        ];

        $colors = ['Brown', 'Black', 'White', 'Black & White', 'Golden', 'Grey'];

        $city = Locations::findByCity(collect(['Delhi', 'Mumbai', 'Ludhiana', 'Jaipur', 'Lucknow', 'Pune', 'Chennai'])->random());

        $name = fake()->unique()->words(2, true);

        return [
            'user_id' => User::factory()->seller(),
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'breed' => fake()->randomElement($breeds),
            'age' => fake()->numberBetween(1, 8).' years',
            'gender' => fake()->randomElement(['male', 'female']),
            'color' => fake()->randomElement($colors),
            'weight' => fake()->numberBetween(20, 600).' kg',
            'is_vaccinated' => fake()->boolean(70),
            'location' => $city['city'],
            'state' => $city['state'],
            'latitude' => $city['lat'],
            'longitude' => $city['lng'],
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 2500),
            'stock' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
