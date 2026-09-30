<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use App\Support\Locations;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@marketplace.test',
        ]);

        $sellers = User::factory()->seller()->count(3)->create();

        User::factory()->count(5)->create();

        $categoryNames = ['Cow', 'Buffalo', 'Goat', 'Dog', 'Cat', 'Bird', 'Sheep', 'Horse'];
        $categories = collect($categoryNames)->map(fn (string $name) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));

        $listings = [
            ['category' => 'Cow', 'name' => 'Holstein Dairy Cow', 'breed' => 'Holstein Friesian', 'age' => '3 years', 'gender' => 'female', 'price' => 850, 'color' => 'Black & White', 'weight' => '450 kg', 'city' => 'Karnal'],
            ['category' => 'Cow', 'name' => 'Sahiwal Milking Cow', 'breed' => 'Sahiwal', 'age' => '4 years', 'gender' => 'female', 'price' => 720, 'color' => 'Reddish Brown', 'weight' => '400 kg', 'city' => 'Jaipur'],
            ['category' => 'Buffalo', 'name' => 'Murrah Buffalo', 'breed' => 'Murrah', 'age' => '5 years', 'gender' => 'female', 'price' => 950, 'color' => 'Black', 'weight' => '550 kg', 'city' => 'Hisar'],
            ['category' => 'Buffalo', 'name' => 'Nili-Ravi Buffalo Calf', 'breed' => 'Nili-Ravi', 'age' => '8 months', 'gender' => 'male', 'price' => 400, 'color' => 'Black', 'weight' => '150 kg', 'city' => 'Ludhiana'],
            ['category' => 'Goat', 'name' => 'Boer Meat Goat', 'breed' => 'Boer', 'age' => '1 year', 'gender' => 'male', 'price' => 180, 'color' => 'White & Brown', 'weight' => '45 kg', 'city' => 'Jodhpur'],
            ['category' => 'Goat', 'name' => 'Jamunapari Dairy Goat', 'breed' => 'Jamunapari', 'age' => '2 years', 'gender' => 'female', 'price' => 220, 'color' => 'White', 'weight' => '55 kg', 'city' => 'Agra'],
            ['category' => 'Dog', 'name' => 'Labrador Retriever Puppy', 'breed' => 'Labrador', 'age' => '3 months', 'gender' => 'male', 'price' => 300, 'color' => 'Golden', 'weight' => '8 kg', 'city' => 'Delhi'],
            ['category' => 'Dog', 'name' => 'Beagle Puppy', 'breed' => 'Beagle', 'age' => '3 months', 'gender' => 'male', 'price' => 260, 'color' => 'Tricolor', 'weight' => '6 kg', 'city' => 'Delhi', 'vaccinated' => false],
            ['category' => 'Dog', 'name' => 'German Shepherd Guard Dog', 'breed' => 'German Shepherd', 'age' => '1 year', 'gender' => 'male', 'price' => 450, 'color' => 'Black & Tan', 'weight' => '30 kg', 'city' => 'Gurugram'],
            ['category' => 'Cat', 'name' => 'Persian Kitten', 'breed' => 'Persian', 'age' => '4 months', 'gender' => 'female', 'price' => 150, 'color' => 'White', 'weight' => '2 kg', 'city' => 'Noida'],
            ['category' => 'Bird', 'name' => 'Parrot Breeding Pair', 'breed' => 'African Grey', 'age' => '2 years', 'gender' => 'unknown', 'price' => 600, 'color' => 'Grey', 'weight' => '0.5 kg', 'city' => 'Mumbai'],
            ['category' => 'Bird', 'name' => 'Rhode Island Red Hens (5)', 'breed' => 'Rhode Island Red', 'age' => '6 months', 'gender' => 'female', 'price' => 90, 'color' => 'Reddish Brown', 'weight' => '2.5 kg', 'city' => 'Chandigarh'],
            ['category' => 'Sheep', 'name' => 'Merino Wool Sheep', 'breed' => 'Merino', 'age' => '2 years', 'gender' => 'female', 'price' => 260, 'color' => 'White', 'weight' => '60 kg', 'city' => 'Ludhiana'],
            ['category' => 'Horse', 'name' => 'Arabian Riding Horse', 'breed' => 'Arabian', 'age' => '6 years', 'gender' => 'male', 'price' => 3200, 'color' => 'Chestnut', 'weight' => '450 kg', 'city' => 'Jaipur'],
        ];

        foreach ($listings as $listing) {
            $city = Locations::findByCity($listing['city']);

            Animal::create([
                'user_id' => $sellers->random()->id,
                'category_id' => $categories->firstWhere('name', $listing['category'])->id,
                'name' => $listing['name'],
                'slug' => Str::slug($listing['name']),
                'breed' => $listing['breed'],
                'age' => $listing['age'],
                'gender' => $listing['gender'],
                'color' => $listing['color'],
                'weight' => $listing['weight'],
                'is_vaccinated' => $listing['vaccinated'] ?? true,
                'location' => $city['city'],
                'state' => $city['state'],
                'latitude' => $city['lat'],
                'longitude' => $city['lng'],
                'description' => "Healthy {$listing['breed']} available for sale. Well cared for and ready for a new home.",
                'price' => $listing['price'],
                'stock' => fake()->numberBetween(1, 6),
                'is_active' => true,
            ]);
        }

        $this->call(BreedingDemoSeeder::class);
        $this->call(VetDemoSeeder::class);
        $this->call(CaretakerDemoSeeder::class);

        $this->command->info('Seeded admin login: admin@marketplace.test / password');
    }
}
