<?php

namespace Database\Factories;

use App\Models\Vet;
use App\Support\Locations;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vet>
 */
class VetFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $city = Locations::findByCity(collect(['Delhi', 'Mumbai', 'Ludhiana', 'Jaipur', 'Lucknow', 'Pune', 'Chennai'])->random());
        $name = 'Dr. '.fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'clinic_name' => fake()->company().' Vet Clinic',
            'qualification' => 'BVSc & AH',
            'experience_years' => fake()->numberBetween(1, 30),
            'services' => 'Vaccination, Surgery',
            'phone' => '+91 00000 '.fake()->numerify('#####'),
            'address' => fake()->streetAddress(),
            'city' => $city['city'],
            'state' => $city['state'],
            'latitude' => $city['lat'],
            'longitude' => $city['lng'],
            'consultation_fee' => 200,
            'home_visit' => false,
            'emergency' => false,
            'is_active' => true,
        ];
    }

    /** Places the doctor in a named city from config/cities.php. */
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
