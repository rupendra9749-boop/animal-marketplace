<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A clean start for a new server: the animal types the forms need, and one admin. No demo animals, doctors,
 * caretakers or users. Safe to run again: it only fills in what is missing.
 *
 * The admin comes from ADMIN_EMAIL / ADMIN_PASSWORD (remove them from .env afterwards).
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Cow', 'Buffalo', 'Goat', 'Dog', 'Cat', 'Bird', 'Sheep', 'Horse'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if ($email && $password && ! User::where('is_admin', true)->exists()) {
            $admin = new User([
                'name' => 'Admin',
                'email' => strtolower($email),
                'password' => Hash::make($password),
                'is_admin' => true,
            ]);
            $admin->email_verified_at = now();
            $admin->is_active = true;
            $admin->save();

            $this->command?->info("Admin created: {$admin->email}");
        }
    }
}
