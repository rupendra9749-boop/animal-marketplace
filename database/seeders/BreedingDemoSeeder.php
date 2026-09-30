<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use App\Support\Locations;
use Illuminate\Database\Seeder;

/**
 * Adds breeding-friendly listings (adult males and females of the same type) so the Breeding
 * animals page and the Breeding Match module have real pairs to try. Safe to run more than
 * once: a listing is created only if its slug does not exist yet, and an existing sample
 * listing is only switched to breeding if it is still a plain "sale" listing.
 */
class BreedingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::where('is_seller', true)->pluck('id');
        if ($sellers->isEmpty()) {
            return;
        }

        // type, slug, name, breed, age, gender, color, weight, city, price, listing type, breeding fee
        $listings = [
            ['Dog', 'labrador-retriever-adult-female', 'Labrador Retriever (Adult Female)', 'Labrador', '2 years', 'female', 'Golden', '27 kg', 'Delhi', 420, 'breeding', 120],
            ['Dog', 'labrador-retriever-stud', 'Labrador Retriever Stud', 'Labrador', '3 years', 'male', 'Black', '32 kg', 'Gurugram', 500, 'breeding', 150],
            ['Dog', 'beagle-adult-female', 'Beagle (Adult Female)', 'Beagle', '2 years', 'female', 'Tricolor', '11 kg', 'Delhi', 380, 'both', 100, false],
            ['Cat', 'persian-tom-cat', 'Persian Tom Cat', 'Persian', '2 years', 'male', 'White', '4.5 kg', 'Noida', 200, 'breeding', 80],
            ['Cow', 'holstein-friesian-bull', 'Holstein Friesian Bull', 'Holstein Friesian', '4 years', 'male', 'Black & White', '900 kg', 'Karnal', 1500, 'breeding', 60],
            ['Buffalo', 'murrah-buffalo-bull', 'Murrah Buffalo Bull', 'Murrah', '5 years', 'male', 'Black', '750 kg', 'Hisar', 1800, 'breeding', 50],
            ['Goat', 'jamunapari-buck', 'Jamunapari Buck', 'Jamunapari', '2 years', 'male', 'White', '60 kg', 'Agra', 260, 'both', 40],
            ['Dog', 'german-shepherd-stud', 'German Shepherd Stud', 'German Shepherd', '3 years', 'male', 'Black & Tan', '34 kg', 'Delhi', 650, 'breeding', 200],
            ['Dog', 'golden-retriever-female', 'Golden Retriever (Adult Female)', 'Golden Retriever', '2 years', 'female', 'Cream', '26 kg', 'Noida', 600, 'breeding', 180],
            ['Cow', 'sahiwal-bull', 'Sahiwal Bull', 'Sahiwal', '4 years', 'male', 'Reddish Brown', '650 kg', 'Jaipur', 1300, 'breeding', 45],
            ['Goat', 'boer-buck', 'Boer Buck', 'Boer', '2 years', 'male', 'White & Brown', '70 kg', 'Jodhpur', 320, 'breeding', 60],
            ['Horse', 'arabian-stallion', 'Arabian Stallion', 'Arabian', '7 years', 'male', 'Grey', '470 kg', 'Jaipur', 4200, 'breeding', 500],
        ];

        foreach ($listings as $row) {
            [$type, $slug, $name, $breed, $age, $gender, $color, $weight, $cityName, $price, $listingType, $fee] = $row;
            $vaccinated = $row[12] ?? true;
            $category = Category::where('name', $type)->first();
            $city = Locations::findByCity($cityName);

            if (! $category || ! $city) {
                continue;
            }

            $animal = Animal::firstOrCreate(['slug' => $slug], [
                'user_id' => $sellers->random(),
                'category_id' => $category->id,
                'name' => $name,
                'breed' => $breed,
                'age' => $age,
                'gender' => $gender,
                'color' => $color,
                'weight' => $weight,
                'is_vaccinated' => $vaccinated,
                'location' => $city['city'],
                'state' => $city['state'],
                'latitude' => $city['lat'],
                'longitude' => $city['lng'],
                'description' => "Healthy {$breed} suitable for responsible breeding. Vet records available on request.",
                'price' => $listingType === Animal::TYPE_BREEDING ? 0 : $price,
                'stock' => 1,
                'is_active' => true,
                'listing_type' => $listingType,
                'breeding_fee' => $fee,
            ]);

            if (! $animal->wasRecentlyCreated && $animal->listing_type === Animal::TYPE_SALE) {
                $animal->update([
                    'listing_type' => $listingType,
                    'breeding_fee' => $fee,
                    'price' => $listingType === Animal::TYPE_BREEDING ? 0 : $animal->price,
                ]);
            }
        }
    }
}
