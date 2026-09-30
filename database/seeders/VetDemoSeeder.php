<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\Locations;
use App\Models\Vet;
use Illuminate\Database\Seeder;

/**
 * Sample animal doctors so the "Find a Vet" search has something to show. Phone numbers are
 * deliberately invalid (they start with 0) so a sample doctor can never ring a real person -
 * replace or delete these from Admin > Animal Doctors. Safe to run more than once: a doctor is
 * only created when its slug does not exist yet.
 */
class VetDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $doctors = [
            ['dr-anil-sharma-delhi', 'Dr. Anil Sharma', 'Delhi Pet & Animal Care', 'BVSc & AH, MVSc', 14, 'Vaccination, Surgery, Dental care, Health check-ups', ['Dog', 'Cat'], 'Delhi', 'Karol Bagh, near Metro Station', 300, 'Mon-Sat, 9 am - 8 pm', false, true, '+91 00000 10001'],
            ['dr-neha-kapoor-gurugram', 'Dr. Neha Kapoor', 'Paws & Claws Clinic', 'BVSc & AH', 8, 'Vaccination, Grooming advice, Deworming, Pet nutrition', ['Dog', 'Cat', 'Bird'], 'Gurugram', 'Sector 14 Market', 350, 'Daily, 10 am - 7 pm', true, false, '+91 00000 10002'],
            ['dr-vikas-yadav-noida', 'Dr. Vikas Yadav', 'Noida Animal Hospital', 'BVSc & AH, PhD', 18, 'Surgery, X-ray & ultrasound, Emergency care, Pregnancy check', ['Dog', 'Cat', 'Horse'], 'Noida', 'Sector 62', 400, 'Open 24 hours', true, true, '+91 00000 10003'],
            ['dr-rajesh-choudhary-jaipur', 'Dr. Rajesh Choudhary', 'Rajasthan Livestock Clinic', 'BVSc & AH, MVSc (Gynaecology)', 21, 'Artificial insemination, Pregnancy diagnosis, Vaccination, Deworming', ['Cow', 'Buffalo', 'Goat', 'Sheep'], 'Jaipur', 'Sanganer Road', 150, 'Mon-Sat, 8 am - 6 pm', true, true, '+91 00000 10004'],
            ['dr-sunita-meena-jaipur', 'Dr. Sunita Meena', 'Pink City Pet Clinic', 'BVSc & AH', 6, 'Vaccination, Skin & coat care, Sterilisation', ['Dog', 'Cat'], 'Jaipur', 'Vaishali Nagar', 300, 'Mon-Sat, 10 am - 7 pm', false, false, '+91 00000 10005'],
            ['dr-harpreet-singh-ludhiana', 'Dr. Harpreet Singh', 'Punjab Dairy Vet Centre', 'BVSc & AH, MVSc (Medicine)', 16, 'Milk-fever & mastitis treatment, Artificial insemination, Vaccination, Foot-and-mouth care', ['Cow', 'Buffalo'], 'Ludhiana', 'Model Town', 200, 'Daily, 7 am - 7 pm', true, true, '+91 00000 10006'],
            ['dr-gurpreet-kaur-chandigarh', 'Dr. Gurpreet Kaur', 'Chandigarh Pet Hospital', 'BVSc & AH, MVSc (Surgery)', 12, 'Surgery, Dental care, Vaccination, Pet boarding', ['Dog', 'Cat', 'Bird'], 'Chandigarh', 'Sector 22-C', 450, 'Mon-Sun, 9 am - 9 pm', false, true, '+91 00000 10007'],
            ['dr-sanjay-malik-karnal', 'Dr. Sanjay Malik', 'Karnal Cattle Clinic', 'BVSc & AH', 19, 'Cattle treatment, Artificial insemination, Vaccination camps, Deworming', ['Cow', 'Buffalo', 'Goat'], 'Karnal', 'Kunjpura Road', 120, 'Daily, 8 am - 6 pm', true, false, '+91 00000 10008'],
            ['dr-pooja-rani-hisar', 'Dr. Pooja Rani', 'Hisar Veterinary Polyclinic', 'BVSc & AH, MVSc', 9, 'Buffalo & cow health, Pregnancy diagnosis, Vaccination, Poultry disease advice', ['Buffalo', 'Cow', 'Bird', 'Goat'], 'Hisar', 'Hansi Road', 150, 'Mon-Sat, 9 am - 5 pm', true, false, '+91 00000 10009'],
            ['dr-amit-verma-agra', 'Dr. Amit Verma', 'Taj City Animal Care', 'BVSc & AH', 11, 'Vaccination, Surgery, Goat & sheep health, Deworming', ['Goat', 'Sheep', 'Dog', 'Cow'], 'Agra', 'Sanjay Place', 250, 'Mon-Sat, 9 am - 7 pm', true, false, '+91 00000 10010'],
            ['dr-mohan-lal-jodhpur', 'Dr. Mohan Lal Bishnoi', 'Marwar Livestock Hospital', 'BVSc & AH, MVSc', 24, 'Camel, horse & sheep treatment, Emergency care, Vaccination', ['Horse', 'Sheep', 'Goat', 'Cow'], 'Jodhpur', 'Basni Industrial Area', 200, 'Open 24 hours', true, true, '+91 00000 10011'],
            ['dr-priya-deshmukh-mumbai', 'Dr. Priya Deshmukh', 'Mumbai Pet & Bird Clinic', 'BVSc & AH, Certified Avian Vet', 10, 'Bird & exotic pet care, Vaccination, Health check-ups, Nutrition plans', ['Bird', 'Dog', 'Cat'], 'Mumbai', 'Andheri West', 500, 'Daily, 10 am - 8 pm', false, false, '+91 00000 10012'],
            ['dr-suresh-patil-pune', 'Dr. Suresh Patil', 'Pune Equine & Large Animal Centre', 'BVSc & AH, MVSc (Surgery)', 20, 'Horse care, Surgery, Lameness treatment, Artificial insemination', ['Horse', 'Cow', 'Buffalo'], 'Pune', 'Hadapsar', 350, 'Mon-Sat, 8 am - 6 pm', true, true, '+91 00000 10013'],
        ];

        foreach ($doctors as [$slug, $name, $clinic, $qualification, $years, $services, $treats, $cityName, $address, $fee, $timings, $home, $emergency, $phone]) {
            $city = Locations::findByCity($cityName);
            if (! $city || Vet::where('slug', $slug)->exists()) {
                continue;
            }

            $vet = Vet::create([
                'name' => $name,
                'slug' => $slug,
                'clinic_name' => $clinic,
                'qualification' => $qualification,
                'experience_years' => $years,
                'services' => $services,
                'about' => "{$name} runs {$clinic} in {$cityName} and has {$years} years of experience treating animals. Call to check timings and book a visit.",
                'phone' => $phone,
                'whatsapp' => $phone,
                'address' => "{$address}, {$cityName}",
                'city' => $city['city'],
                'state' => $city['state'],
                'latitude' => $city['lat'],
                'longitude' => $city['lng'],
                'consultation_fee' => $fee,
                'timings' => $timings,
                'home_visit' => $home,
                'emergency' => $emergency,
                'is_active' => true,
            ]);

            $vet->categories()->sync(collect($treats)->map(fn ($type) => $categories[$type] ?? null)->filter()->all());
        }
    }
}
