<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeStatusFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_verified_status_pill(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Check that all 4 status pills render in the tab group
        $response->assertSee('All');
        $response->assertSee('Available');
        $response->assertSee('Pending');
        $response->assertSee('Verified');
        $response->assertSee("setHomeStatus('verified')", false);
    }

    public function test_verified_status_tab_filters_by_admin_verified_accounts(): void
    {
        $verifiedUser = User::create([
            'name' => 'Dr. Maria Santos (Verified Rescuer)',
            'email' => 'maria.verified@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => true,
        ]);

        $regularUser = User::create([
            'name' => 'Regular Rescuer',
            'email' => 'regular@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => false,
        ]);

        // Verified user has 2 pets
        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Bruno Verified Pup',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '4 Months',
            'gender' => 'Male',
            'description' => 'From verified rescuer',
            'status' => 'Available',
            'posted_by' => $verifiedUser->name,
            'is_verified' => false,
        ]);

        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Luna Verified Cat',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '1 year',
            'gender' => 'Female',
            'description' => 'Also from verified rescuer',
            'status' => 'Pending',
            'posted_by' => $verifiedUser->name,
            'is_verified' => false,
        ]);

        // Regular user has 1 pet (not verified)
        Pet::create([
            'user_id' => $regularUser->id,
            'name' => 'Rocky Regular Dog',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '2 years',
            'gender' => 'Male',
            'description' => 'From regular rescuer',
            'status' => 'Available',
            'posted_by' => $regularUser->name,
            'is_verified' => false,
        ]);

        // Also a pet specifically marked as is_verified
        Pet::create([
            'user_id' => $regularUser->id,
            'name' => 'Milo Admin Verified Pet',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Directly verified pet',
            'status' => 'Available',
            'posted_by' => $regularUser->name,
            'is_verified' => true,
        ]);

        // 1. Visit Home without filter (All)
        $allResponse = $this->get('/');
        $allResponse->assertStatus(200);
        // Verified count should be 3 (2 from verifiedUser + 1 directly verified pet)
        $allResponse->assertSee('Bruno Verified Pup');
        $allResponse->assertSee('Luna Verified Cat');
        $allResponse->assertSee('Rocky Regular Dog');
        $allResponse->assertSee('Milo Admin Verified Pet');

        // 2. Visit with ?status=verified
        $verifiedResponse = $this->get('/?status=verified');
        $verifiedResponse->assertStatus(200);

        // Should see pets from verified user & verified pets
        $verifiedResponse->assertSee('Bruno Verified Pup');
        $verifiedResponse->assertSee('Luna Verified Cat');
        $verifiedResponse->assertSee('Milo Admin Verified Pet');

        // Should NOT see unverified pets from regular user
        $verifiedResponse->assertDontSee('Rocky Regular Dog');

        // Should show "Showing 3 pets"
        $verifiedResponse->assertSee('Showing 3 pets');
    }

    public function test_dynamic_badge_counters_on_status_pills(): void
    {
        $verifiedUser = User::create([
            'name' => 'Verified Rescuer',
            'email' => 'rescuer@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => true,
        ]);

        $unverifiedUser = User::create([
            'name' => 'Unverified Rescuer',
            'email' => 'unverified@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => false,
        ]);

        // 2 Available, 1 Pending from verifiedUser
        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Pet One',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 year',
            'gender' => 'Male',
            'description' => 'A sweet dog',
            'status' => 'Available',
            'posted_by' => $verifiedUser->name,
        ]);

        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Pet Two',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '2 years',
            'gender' => 'Female',
            'description' => 'A gentle dog',
            'status' => 'Available',
            'posted_by' => $verifiedUser->name,
        ]);

        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Pet Three',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '5 Months',
            'gender' => 'Male',
            'description' => 'A friendly cat',
            'status' => 'Pending',
            'posted_by' => $verifiedUser->name,
        ]);

        // 1 Available from unverifiedUser
        Pet::create([
            'user_id' => $unverifiedUser->id,
            'name' => 'Pet Four',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 years',
            'gender' => 'Male',
            'description' => 'An active dog',
            'status' => 'Available',
            'posted_by' => $unverifiedUser->name,
        ]);

        $response = $this->get('/?status=verified');
        $response->assertStatus(200);

        // Total all = 4, Available = 3, Pending = 1, Verified = 3
        $statusCounts = $response->viewData('statusCounts');
        $this->assertEquals(4, $statusCounts['all']);
        $this->assertEquals(3, $statusCounts['available']);
        $this->assertEquals(1, $statusCounts['pending']);
        $this->assertEquals(3, $statusCounts['verified']);

        // Check active class on the verified status pill
        $response->assertSee('home-status-pill active', false);
    }

    public function test_pet_card_displays_verified_badge_for_verified_user(): void
    {
        $verifiedUser = User::create([
            'name' => 'Verified Rescuer Hero',
            'email' => 'hero.verified@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => true,
        ]);

        $unverifiedUser = User::create([
            'name' => 'Unverified Rescuer Novice',
            'email' => 'novice.unverified@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => false,
        ]);

        Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Champion Verified Pet',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 year',
            'gender' => 'Male',
            'description' => 'A verified dog',
            'status' => 'Available',
            'posted_by' => $verifiedUser->name,
        ]);

        Pet::create([
            'user_id' => $unverifiedUser->id,
            'name' => 'Ordinary Unverified Pet',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '2 years',
            'gender' => 'Female',
            'description' => 'An unverified cat',
            'status' => 'Available',
            'posted_by' => $unverifiedUser->name,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verified pet card should render the verified badge
        $response->assertSee('pet-verified-badge');
        $response->assertSee('Verified');
        $response->assertSee('✓');

        // Ensure the response renders the pet names
        $response->assertSee('Champion Verified Pet');
        $response->assertSee('Ordinary Unverified Pet');
    }

    public function test_adopted_pets_and_filter_tab_are_not_shown_on_home_page(): void
    {
        $user = User::create([
            'name' => 'Rescuer User',
            'email' => 'rescuer.adopted@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
            'is_verified' => false,
        ]);

        Pet::create([
            'user_id' => $user->id,
            'name' => 'Active Adoptable Pet',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 year',
            'gender' => 'Male',
            'description' => 'Still available',
            'status' => 'Available',
            'posted_by' => $user->name,
        ]);

        Pet::create([
            'user_id' => $user->id,
            'name' => 'Already Adopted Pet',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '2 years',
            'gender' => 'Female',
            'description' => 'Happy in new home',
            'status' => 'Adopted',
            'posted_by' => $user->name,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Adopted filter tab must NOT exist
        $response->assertDontSee("setHomeStatus('adopted')", false);

        // Active pet must be visible
        $response->assertSee('Active Adoptable Pet');

        // Adopted pet must NOT appear on the home feed
        $response->assertDontSee('Already Adopted Pet');
    }
}
