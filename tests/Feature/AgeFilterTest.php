<?php

namespace Tests\Feature;

use App\Http\Controllers\HomeController;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_matches_age_filter_classifies_correctly(): void
    {
        // -----------------------------------------------------------------------
        // Puppy / Kitten: 0 - 6 months
        // -----------------------------------------------------------------------
        $this->assertTrue(HomeController::matchesAgeFilter('3 months', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('6 months', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('5 months', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('8 weeks', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('Puppy', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('Kitten', 'puppy_kitten'));
        // 7 months is already Junior — NOT puppy_kitten
        $this->assertFalse(HomeController::matchesAgeFilter('7 months', 'puppy_kitten'));
        $this->assertFalse(HomeController::matchesAgeFilter('1 year old', 'puppy_kitten'));
        $this->assertFalse(HomeController::matchesAgeFilter('22 years old', 'puppy_kitten'));

        // -----------------------------------------------------------------------
        // Junior: 7 - 12 months (> 6 months, up to and including 12 months)
        // Note: "1 year old" expressed in years goes to Adult (years path uses < 1.0)
        // -----------------------------------------------------------------------
        $this->assertTrue(HomeController::matchesAgeFilter('7 months', 'junior'));
        $this->assertTrue(HomeController::matchesAgeFilter('10 months', 'junior'));
        $this->assertTrue(HomeController::matchesAgeFilter('11 months', 'junior'));
        $this->assertTrue(HomeController::matchesAgeFilter('12 months', 'junior'));  // 12 months → junior via months path
        $this->assertTrue(HomeController::matchesAgeFilter('Junior', 'junior'));
        // Also maps 'young' keyword to junior bucket
        $this->assertTrue(HomeController::matchesAgeFilter('Young', 'junior'));
        $this->assertFalse(HomeController::matchesAgeFilter('5 months', 'junior'));
        // "1 year old" uses the years parser → < 1.0 is false → adult
        $this->assertFalse(HomeController::matchesAgeFilter('1 year old', 'junior'));
        $this->assertFalse(HomeController::matchesAgeFilter('2 years old', 'junior'));

        // -----------------------------------------------------------------------
        // Adult: 1 - 7 years (> 12 months / >= 1.0 years)
        // -----------------------------------------------------------------------
        $this->assertTrue(HomeController::matchesAgeFilter('1 year old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('1 yr', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('1.5 yrs', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('3 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('5 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('7 years', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('Adult', 'adult'));
        $this->assertFalse(HomeController::matchesAgeFilter('5 months', 'adult'));
        $this->assertFalse(HomeController::matchesAgeFilter('6 months', 'adult'));
        // 12 months goes to junior (months path) — not adult
        $this->assertFalse(HomeController::matchesAgeFilter('12 months', 'adult'));
        // 8 years+ is Senior — NOT adult
        $this->assertFalse(HomeController::matchesAgeFilter('8 years old', 'adult'));
        // 22 years is unrealistic — classifies nowhere
        $this->assertFalse(HomeController::matchesAgeFilter('22 years old', 'adult'));

        // -----------------------------------------------------------------------
        // Senior: 8+ years (up to 20 years)
        // -----------------------------------------------------------------------
        $this->assertTrue(HomeController::matchesAgeFilter('8 years', 'senior'));
        $this->assertTrue(HomeController::matchesAgeFilter('10 years old', 'senior'));
        $this->assertTrue(HomeController::matchesAgeFilter('15 years old', 'senior'));
        $this->assertTrue(HomeController::matchesAgeFilter('20 years', 'senior'));
        $this->assertTrue(HomeController::matchesAgeFilter('Senior', 'senior'));
        $this->assertFalse(HomeController::matchesAgeFilter('5 months', 'senior'));
        $this->assertFalse(HomeController::matchesAgeFilter('7 years', 'senior'));
        // 22 years is unrealistic — classifies nowhere
        $this->assertFalse(HomeController::matchesAgeFilter('22 years old', 'senior'));
    }

    public function test_home_page_age_filter_requests(): void
    {
        $user = User::create([
            'name' => 'Rescuer Juan',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Puppy (3 months)
        $pet1 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Bantay Puppy',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 months',
            'gender' => 'Male',
            'description' => 'Cute pup',
            'status' => 'Available',
            'is_verified' => true,
            'posted_by' => $user->name,
        ]);

        // Junior (9 months)
        $pet2 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Tagpi Junior',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '9 months',
            'gender' => 'Female',
            'description' => 'Energetic junior dog',
            'status' => 'Available',
            'is_verified' => true,
            'posted_by' => $user->name,
        ]);

        // Adult (3 years)
        $pet3 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Muning Adult',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '3 years',
            'gender' => 'Female',
            'description' => 'Calm adult cat',
            'status' => 'Available',
            'is_verified' => true,
            'posted_by' => $user->name,
        ]);

        // Senior (10 years old)
        $pet4 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Tibo Senior',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '10 years old',
            'gender' => 'Male',
            'description' => 'Calm senior dog',
            'status' => 'Available',
            'is_verified' => true,
            'posted_by' => $user->name,
        ]);

        // Filter Puppy / Kitten
        $response = $this->get('/?age=puppy_kitten');
        $response->assertStatus(200);
        $response->assertSee('Bantay Puppy');
        $response->assertDontSee('Tagpi Junior');
        $response->assertDontSee('Muning Adult');
        $response->assertDontSee('Tibo Senior');

        // Filter Junior
        $response = $this->get('/?age=junior');
        $response->assertStatus(200);
        $response->assertSee('Tagpi Junior');
        $response->assertDontSee('Bantay Puppy');
        $response->assertDontSee('Muning Adult');
        $response->assertDontSee('Tibo Senior');

        // Filter Adult
        $response = $this->get('/?age=adult');
        $response->assertStatus(200);
        $response->assertSee('Muning Adult');
        $response->assertDontSee('Bantay Puppy');
        $response->assertDontSee('Tagpi Junior');
        $response->assertDontSee('Tibo Senior');

        // Filter Senior
        $response = $this->get('/?age=senior');
        $response->assertStatus(200);
        $response->assertSee('Tibo Senior');
        $response->assertDontSee('Bantay Puppy');
        $response->assertDontSee('Tagpi Junior');
        $response->assertDontSee('Muning Adult');
    }
}
