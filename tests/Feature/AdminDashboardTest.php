<?php

namespace Tests\Feature;

use App\Models\AdoptionRequest;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_redirect_routes_to_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_admin_dashboard_renders_with_live_statistics(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular Adopter',
            'email'    => 'adopter@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        $pet = Pet::create([
            'user_id'            => $user->id,
            'name'               => 'Bantay',
            'species'            => 'Dog',
            'breed'              => 'Aspin',
            'age'                => '5 months',
            'gender'             => 'Male',
            'description'        => 'A friendly rescue pup',
            'status'             => 'Available',
            'posted_by'          => $user->name,
        ]);

        AdoptionRequest::create([
            'pet_id'             => $pet->id,
            'user_id'            => $user->id,
            'applicant_name'     => 'Regular Adopter',
            'contact_number'     => '09123456789',
            'email'              => 'adopter@example.com',
            'status'             => 'Pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Overview of pets listed in Brgy. 171.');
        $response->assertSee('Total posts');
        $response->assertSee('Dogs');
        $response->assertSee('Cats');
        $response->assertSee('Active accounts');
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/');
    }

    public function test_regular_user_is_forbidden_from_admin_dashboard(): void
    {
        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'regular@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_admin_adoptme_account_can_login_and_redirects_to_admin_dashboard(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email'    => 'adoptme@gmail.com',
            'password' => 'admin123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->is_admin);

        // Follow redirect to dashboard
        $dashResponse = $this->get('/admin/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Kupkop Administrator');
    }

    public function test_admin_dashboard_renders_species_analytics_counter_and_breakdown(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'regular@example.com',
            'password' => bcrypt('password123'),
        ]);

        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Doggo',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 yr',
            'gender'      => 'Male',
            'description' => 'A sweet dog',
            'status'      => 'Available',
            'posted_by'   => $user->name,
        ]);

        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Kitty',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '2 yrs',
            'gender'      => 'Female',
            'description' => 'A sweet cat',
            'status'      => 'Available',
            'posted_by'   => $user->name,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dogs vs. cats');
        $response->assertSee('By status');
        $response->assertSee('Available');
        $response->assertSee('Pending');
        $response->assertSee('Dogs');
        $response->assertSee('Cats');
        $response->assertSee('Others');
    }

    public function test_admin_dashboard_renders_posting_average_analytics_with_date_filters(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Average posts');
        $response->assertSee('posts per day on average');
        $response->assertSee('7 days');
        $response->assertSee('1 month');
        $response->assertSee('1 year');
        $response->assertSee('Calendar');
    }

    public function test_admin_dashboard_handles_different_date_filters_and_custom_range(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        // 1 Month filter
        $responseMonth = $this->actingAs($admin)->get('/admin/dashboard?period=1m');
        $responseMonth->assertStatus(200);
        $responseMonth->assertSee('1 month');

        // 1 Year filter
        $responseYear = $this->actingAs($admin)->get('/admin/dashboard?period=1y');
        $responseYear->assertStatus(200);
        $responseYear->assertSee('1 year');

        // Custom Date Range filter
        $responseCustom = $this->actingAs($admin)->get('/admin/dashboard?period=custom&from=2026-10-01&to=2026-10-20');
        $responseCustom->assertStatus(200);
        $responseCustom->assertSee('Calendar');
    }

    public function test_admin_dashboard_ajax_returns_json_with_rendered_html(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/dashboard?period=7d');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'period',
                'period_label',
                'days_count',
                'unique_posters_count',
                'total_period_posts',
                'avg_active_users',
                'avg_posts_per_user',
                'chart_points',
            ],
            'html',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_dashboard_renders_calendar_view(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard?period=calendar');
        $response->assertStatus(200);
        $response->assertSee('cal-grid-wrap');
        $response->assertSee('cal-month-nav');
        $response->assertSee('darker means more posts');
    }

    public function test_admin_dashboard_species_analytics_accounts_for_others_category(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Pet Rescuer',
            'email'    => 'rescuer@example.com',
            'password' => bcrypt('password123'),
        ]);

        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Bunny',
            'species'     => 'Rabbit',
            'breed'       => 'Holland Lop',
            'age'         => '8 months',
            'gender'      => 'Female',
            'description' => 'A sweet bunny',
            'status'      => 'Available',
            'posted_by'   => $user->name,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Others · 1');
    }

    public function test_admin_dashboard_renders_empty_state_for_attention_and_recent_activity(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('All caught up!');
        $response->assertSee('No items need your attention right now.');
        $response->assertSee('No recent activity');
    }

    public function test_admin_dashboard_renders_dynamic_attention_and_recent_activity_when_data_exists(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Maria Santos',
            'email'    => 'maria@example.com',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        $pet = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Brownie',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 yr',
            'gender'      => 'Male',
            'description' => 'Friendly rescue',
            'status'      => 'Pending',
            'posted_by'   => $user->name,
        ]);

        AdoptionRequest::create([
            'pet_id'         => $pet->id,
            'user_id'        => $user->id,
            'applicant_name' => 'Adopter One',
            'contact_number' => '09123456789',
            'email'          => 'adopter1@example.com',
            'status'         => 'Pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('1 post to verify');
        $response->assertSee('1 adoption request');
        $response->assertSee('Maria Santos created an account');
        $response->assertSee('Brownie was posted by Maria Santos');
    }

    public function test_admin_dashboard_renders_logout_confirmation_prompt(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('confirm-admin-logout-modal');
        $response->assertSee('Log Out of Admin?');
        $response->assertSee('Are you sure you want to log out of the Admin Portal?');
        $response->assertSee('showAdminLogoutConfirm()', false);
    }

    public function test_admin_dashboard_renders_sidebar_logo(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('images/logo.png');
        $response->assertSee('alt="Kupkop Logo"', false);
        $response->assertSee('badge-admin');
        $response->assertSee('ADMIN');
    }
}
