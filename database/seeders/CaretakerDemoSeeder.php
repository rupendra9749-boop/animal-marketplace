<?php

namespace Database\Seeders;

use App\Models\Caretaker;
use App\Models\Category;
use App\Support\Locations;
use Illuminate\Database\Seeder;

/**
 * Sample animal caretakers so the "Caretakers" search has something to show. Phone numbers are deliberately
 * invalid (they start with 0) so a sample caretaker can never ring a real person - replace or delete them
 * from Admin > Caretakers. Safe to run more than once: a caretaker is only created when its slug is new.
 */
class CaretakerDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        // slug, name, headline, services, animals, city, area, rate, unit, years, comes to you, boarding, available, phone
        $people = [
            ['ramesh-kumar-delhi', 'Ramesh Kumar', 'Dog walker and pet sitter for busy families', 'Dog walking, Daily feeding, Pet sitting, Grooming', ['Dog', 'Cat'], 'Delhi', 'Dwarka', 400, 'day', 6, true, false, true, '+91 00000 20001'],
            ['sunita-devi-gurugram', 'Sunita Devi', 'Home boarding for dogs and cats while you travel', 'Boarding, Daily feeding, Play time, Medication help', ['Dog', 'Cat'], 'Gurugram', 'Sector 45', 600, 'day', 4, false, true, true, '+91 00000 20002'],
            ['mohan-singh-noida', 'Mohan Singh', 'Farm hand for cows and buffaloes', 'Milking, Feeding & fodder, Cleaning, Calf care', ['Cow', 'Buffalo'], 'Noida', 'Sector 78', 12000, 'month', 12, true, false, true, '+91 00000 20003'],
            ['kavita-sharma-jaipur', 'Kavita Sharma', 'Goat and sheep caretaker', 'Feeding, Grazing, Health watch, Kidding help', ['Goat', 'Sheep'], 'Jaipur', 'Sanganer', 350, 'day', 7, true, false, true, '+91 00000 20004'],
            ['harjinder-singh-ludhiana', 'Harjinder Singh', 'Dairy caretaker with 15 years on Punjab farms', 'Milking, Feeding & fodder, Cleaning, Calf care, Vaccination help', ['Cow', 'Buffalo'], 'Ludhiana', 'Model Town', 14000, 'month', 15, true, false, true, '+91 00000 20005'],
            ['priya-nair-kochi', 'Priya Nair', 'Loving care for pets, at your home or mine', 'Pet sitting, Boarding, Grooming, Bird care', ['Dog', 'Cat', 'Bird'], 'Kochi', 'Kakkanad', 500, 'day', 5, true, true, true, '+91 00000 20006'],
            ['imran-khan-lucknow', 'Imran Khan', 'Horse groom and stable hand', 'Grooming, Exercise, Feeding, Stable cleaning', ['Horse'], 'Lucknow', 'Gomti Nagar', 700, 'day', 9, true, false, false, '+91 00000 20007'],
            ['lakshmi-devi-hyderabad', 'Lakshmi Devi', 'Poultry and small animal caretaker', 'Feeding, Egg collection, Cleaning, Health watch', ['Bird', 'Goat'], 'Hyderabad', 'Shamirpet', 300, 'day', 6, true, false, true, '+91 00000 20008'],
            ['arjun-patil-pune', 'Arjun Patil', 'Weekend and holiday pet care', 'Dog walking, Pet sitting, Training basics, Transport to vet', ['Dog', 'Cat'], 'Pune', 'Hinjewadi', 250, 'visit', 3, true, false, true, '+91 00000 20009'],
            ['gurmeet-kaur-amritsar', 'Gurmeet Kaur', 'Boarding for cows and calves during travel or illness', 'Boarding, Milking, Feeding & fodder, Calf care', ['Cow', 'Buffalo', 'Goat'], 'Amritsar', 'Ajnala Road', 500, 'day', 10, false, true, true, '+91 00000 20010'],
        ];

        foreach ($people as [$slug, $name, $headline, $services, $animals, $cityName, $area, $rate, $unit, $years, $home, $boarding, $available, $phone]) {
            $place = Locations::findByCity($cityName);
            if (! $place || Caretaker::where('slug', $slug)->exists()) {
                continue;
            }

            $caretaker = Caretaker::create([
                'name' => $name,
                'slug' => $slug,
                'headline' => $headline,
                'services' => $services,
                'about' => "{$name} looks after animals in and around {$place['city']} and has {$years} years of experience. Call to talk about what you need and agree the work and payment.",
                'experience_years' => $years,
                'rate' => $rate,
                'rate_unit' => $unit,
                'phone' => $phone,
                'whatsapp' => $phone,
                'address' => "{$area}, {$place['city']}",
                'state' => $place['state'],
                'city' => $place['city'],
                'latitude' => $place['lat'],
                'longitude' => $place['lng'],
                'home_visit' => $home,
                'boarding' => $boarding,
                'available' => $available,
                'is_active' => true,
            ]);

            $caretaker->categories()->sync(collect($animals)->map(fn ($type) => $categories[$type] ?? null)->filter()->all());
        }
    }
}
