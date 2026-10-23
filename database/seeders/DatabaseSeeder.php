<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pet;
use App\Models\AdoptionRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Admin Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@kupkop.test'],
            [
                'name'     => 'Shelter Admin',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'adoptme@gmail.com'],
            [
                'name'     => 'Kupkop Administrator',
                'password' => Hash::make('admin123!'),
                'is_admin' => true,
            ]
        );

        // 2. Create a Regular User for testing
        $user = User::firstOrCreate(
            ['email' => 'user@kupkop.test'],
            [
                'name'     => 'Regular Adopter',
                'password' => Hash::make('password123'),
                'is_admin' => false,
            ]
        );

        // 3. Seed initial 4 barangay pets so the feed has rich initial data
        $bantay = Pet::firstOrCreate(
            ['name' => 'Bantay'],
            [
                'user_id' => $user->id,
                'species' => 'Dog',
                'breed' => 'Aspin',
                'age' => '5 months',
                'gender' => 'Male',
                'vaccination_status' => 'Vaccinated (1st 5-in-1 shot)',
                'deworming_status' => 'Dewormed',
                'is_spayed_neutered' => false,
                'special_health_notes' => 'Healthy & active, no known allergies',
                'description' => 'Very playful brown puppy rescued near basketball court. Vaccinated with 1st shot.',
                'image_path' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0492',
                'posted_by' => 'Kagawad Elena Santos (Barangay Animal Welfare Committee)',
            ]
        );

        $miming = Pet::firstOrCreate(
            ['name' => 'Miming'],
            [
                'user_id' => $user->id,
                'species' => 'Cat',
                'breed' => 'Calico Puspin',
                'age' => '1.5 yrs',
                'gender' => 'Female',
                'vaccination_status' => 'Complete Rabies & Tricat vaccine',
                'deworming_status' => 'Dewormed',
                'is_spayed_neutered' => true,
                'special_health_notes' => 'Indoor only, litter trained',
                'description' => 'Gentle, quiet indoor cat. Litter box trained and very sweet with children.',
                'image_path' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0488',
                'posted_by' => 'Barangay Health & Sanitation Office',
            ]
        );

        $brownie = Pet::firstOrCreate(
            ['name' => 'Brownie'],
            [
                'user_id' => $user->id,
                'species' => 'Dog',
                'breed' => 'Golden Aspin',
                'age' => '2 yrs',
                'gender' => 'Male',
                'vaccination_status' => 'Complete anti-rabies, 5-in-1',
                'deworming_status' => 'Dewormed',
                'is_spayed_neutered' => true,
                'special_health_notes' => 'Leash trained, friendly with other dogs',
                'description' => 'Friendly community dog that loves belly rubs and outdoor walks. Neutered.',
                'image_path' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0475',
                'posted_by' => 'Kagawad Elena Santos (Barangay Animal Welfare Committee)',
            ]
        );

        $oreo = Pet::firstOrCreate(
            ['name' => 'Oreo'],
            [
                'user_id' => $user->id,
                'species' => 'Cat',
                'breed' => 'Tuxedo Puspin',
                'age' => '8 months',
                'gender' => 'Male',
                'vaccination_status' => 'Initial deworming completed, Rabies scheduled',
                'deworming_status' => 'Dewormed',
                'is_spayed_neutered' => false,
                'special_health_notes' => 'Very energetic and healthy',
                'description' => 'Curious tuxedo kitten with shiny black coat. Very energetic and healthy.',
                'image_path' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',
                'status' => 'Pending',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0501',
                'posted_by' => 'Tanod Rescue Volunteer Desk',
            ]
        );

        // 4. Seed sample incoming adoption request for Bantay
        AdoptionRequest::firstOrCreate(
            ['applicant_name' => 'Ana Marie Dela Cruz', 'pet_id' => $bantay->id],
            [
                'user_id' => $user->id,
                'contact_number' => '0917-555-8921',
                'email' => 'anamarie@example.com',
                'barangay' => 'Brgy. San Antonio',
                'message' => 'Our family would love to adopt Bantay! We have a fenced front yard and two kids who love dogs.',
                'status' => 'Pending',
            ]
        );
    }
}
