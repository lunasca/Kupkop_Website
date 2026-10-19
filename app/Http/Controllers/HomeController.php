<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page with pet listings.
     */
    public function index(): View
    {
        $pets = [
            [
                'id' => 1,
                'name' => 'Bantay',
                'species' => 'Dog',
                'species_label' => 'Dog (Canine)',
                'breed' => 'Aspin',
                'breed_full' => 'Philippine Aspin (Mixed Breed)',
                'age' => '5 months',
                'age_full' => '5 Months Old (Puppy)',
                'gender' => 'Male',
                'gender_full' => 'Male (Intact)',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0492',
                'posted_by' => 'Kagawad Elena Santos (Barangay Animal Welfare Committee)',
                'rescue_date' => 'October 14, 2024',
                'health_condition' => 'Vaccinated (1st 5-in-1 shot), Dewormed, Healthy & active',
                'description' => 'Very playful brown puppy rescued near basketball court. Vaccinated with 1st shot.',
                'story' => 'Bantay was rescued wandering near the barangay covered basketball court during a rainstorm. He is energetic, very loving, accustomed to children, and already responds well to gentle commands. He is looking for a forever family with a secure yard or indoor accommodations.',
                'image' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1537151625747-768eb6cf92b2?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=300&q=80',
                ],
            ],
            [
                'id' => 2,
                'name' => 'Miming',
                'species' => 'Cat',
                'species_label' => 'Cat (Feline)',
                'breed' => 'Calico Puspin',
                'breed_full' => 'Philippine Puspin (Calico Domestic Shorthair)',
                'age' => '1.5 yrs',
                'age_full' => '1.5 Years Old (Young Adult)',
                'gender' => 'Female',
                'gender_full' => 'Female (Spayed)',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0488',
                'posted_by' => 'Barangay Health & Sanitation Office',
                'rescue_date' => 'September 28, 2024',
                'health_condition' => 'Complete Rabies & Tricat vaccine, Spayed, Dewormed',
                'description' => 'Gentle, quiet indoor cat. Litter box trained and very sweet with children.',
                'story' => 'Miming was found sheltering under a community health center bench. She is extremely docile, purrs immediately when petted, and loves sleeping on soft blankets. Ideal for quiet apartments or families with calm kids.',
                'image' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=300&q=80',
                ],
            ],
            [
                'id' => 3,
                'name' => 'Brownie',
                'species' => 'Dog',
                'species_label' => 'Dog (Canine)',
                'breed' => 'Golden Aspin',
                'breed_full' => 'Philippine Aspin (Golden Coat)',
                'age' => '2 yrs',
                'age_full' => '2 Years Old (Adult)',
                'gender' => 'Male',
                'gender_full' => 'Male (Neutered)',
                'status' => 'Available',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0475',
                'posted_by' => 'Kagawad Elena Santos (Barangay Animal Welfare Committee)',
                'rescue_date' => 'August 19, 2024',
                'health_condition' => 'Complete anti-rabies, 5-in-1, Neutered, Healthy',
                'description' => 'Friendly community dog that loves belly rubs and outdoor walks. Neutered.',
                'story' => 'Brownie was a beloved community pet cared for by local market vendors before being brought into foster care for formal adoption. He walks well on a leash, loves outdoor playtime, and gets along well with other dogs.',
                'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1561037404-61cd46aa615b?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1507146426996-ef05306b995a?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=300&q=80',
                ],
            ],
            [
                'id' => 4,
                'name' => 'Oreo',
                'species' => 'Cat',
                'species_label' => 'Cat (Feline)',
                'breed' => 'Tuxedo Puspin',
                'breed_full' => 'Philippine Puspin (Tuxedo Shorthair)',
                'age' => '8 months',
                'age_full' => '8 Months Old (Juvenile)',
                'gender' => 'Male',
                'gender_full' => 'Male (Intact)',
                'status' => 'Pending',
                'location' => 'Brgy. San Antonio, Pasig City',
                'rescuer_id' => '#RES-0501',
                'posted_by' => 'Tanod Rescue Volunteer Desk',
                'rescue_date' => 'November 02, 2024',
                'health_condition' => 'Initial deworming completed, Rabies vaccine scheduled, Very playful',
                'description' => 'Curious tuxedo kitten with shiny black coat. Very energetic and healthy.',
                'story' => 'Oreo was found by barangay tanods inside an engine compartment on a chilly evening. He has bounced back with unlimited curiosity, loves chasing paper balls, and purrs non-stop during feeding time.',
                'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?auto=format&fit=crop&w=800&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=300&q=80',
                    'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=300&q=80',
                ],
            ],
        ];

        return view('pages.home', compact('pets'));
    }
}
