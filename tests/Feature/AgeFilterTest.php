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
        // Puppy / Kitten (< 1 yr)
        $this->assertTrue(HomeController::matchesAgeFilter('5 months', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('3 Months', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('8 weeks', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('Puppy', 'puppy_kitten'));
        $this->assertTrue(HomeController::matchesAgeFilter('Kitten', 'puppy_kitten'));
        $this->assertFalse(HomeController::matchesAgeFilter('1 year old', 'puppy_kitten'));
        $this->assertFalse(HomeController::matchesAgeFilter('22 years old', 'puppy_kitten'));

        // Young (1 - 2 yrs)
        $this->assertTrue(HomeController::matchesAgeFilter('1 year old', 'young'));
        $this->assertTrue(HomeController::matchesAgeFilter('1 yr', 'young'));
        $this->assertTrue(HomeController::matchesAgeFilter('1.5 yrs', 'young'));
        $this->assertTrue(HomeController::matchesAgeFilter('2 years old', 'young'));
        $this->assertTrue(HomeController::matchesAgeFilter('Young', 'young'));
        $this->assertFalse(HomeController::matchesAgeFilter('5 months', 'young'));
        $this->assertFalse(HomeController::matchesAgeFilter('3 years old', 'young'));
        // 22 years old must NOT be classified as young (fixes '%2 year%' bug)
        $this->assertFalse(HomeController::matchesAgeFilter('22 years old', 'young'));

        // Adult (2+ yrs)
        $this->assertTrue(HomeController::matchesAgeFilter('2 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('3 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('5 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('22 years old', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('Adult', 'adult'));
        $this->assertTrue(HomeController::matchesAgeFilter('Senior', 'adult'));
        $this->assertFalse(HomeController::matchesAgeFilter('5 months', 'adult'));
        $this->assertFalse(HomeController::matchesAgeFilter('1 year old', 'adult'));
    }

    public function test_home_page_age_filter_requests(): void
    {
        $user = User::create([
            'name' => 'Rescuer Juan',
            'email' => 'juan@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Puppy
        $pet1 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Bantay Puppy',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '5 months',
            'gender' => 'Male',
            'description' => 'Cute pup',
            'status' => 'Available',
            'posted_by' => $user->name,
        ]);

        // Young
        $pet2 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Tagpi Young',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 year old',
            'gender' => 'Female',
            'description' => 'Playful young dog',
            'status' => 'Available',
            'posted_by' => $user->name,
        ]);

        // Adult
        $pet3 = Pet::create([
            'user_id' => $user->id,
            'name' => 'Muning Senior',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '5 years old',
            'gender' => 'Female',
            'description' => 'Calm adult cat',
            'status' => 'Available',
            'posted_by' => $user->name,
        ]);

        // Filter Puppy / Kitten
        $response = $this->get('/?age=puppy_kitten');
        $response->assertStatus(200);
        $response->assertSee('Bantay Puppy');
        $response->assertDontSee('Tagpi Young');
        $response->assertDontSee('Muning Senior');

        // Filter Young
        $response = $this->get('/?age=young');
        $response->assertStatus(200);
        $response->assertSee('Tagpi Young');
        $response->assertDontSee('Bantay Puppy');
        $response->assertDontSee('Muning Senior');

        // Filter Adult
        $response = $this->get('/?age=adult');
        $response->assertStatus(200);
        $response->assertSee('Muning Senior');
        $response->assertDontSee('Bantay Puppy');
        $response->assertDontSee('Tagpi Young');
    }
}
