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
        $response->assertSee('Overview of pets listed in Brgy. 176-F.');
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
        $response->assertDontSee('1 adoption request');
        $response->assertDontSee('received an adoption request');
        $response->assertDontSee('/my-posts');
        $response->assertSee('Maria Santos created an account');
        $response->assertSee('Brownie was posted by Maria Santos');
    }

    public function test_admin_dashboard_renders_blocked_accounts_in_needs_your_attention(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        User::create([
            'name'       => 'Suspended Bad User',
            'email'      => 'suspended@example.com',
            'password'   => bcrypt('password123'),
            'is_admin'   => false,
            'is_blocked' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('1 blocked account');
        $response->assertSee('Review suspended user accounts');
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
        $response->assertDontSee('badge-admin');
        $response->assertSee('Settings');
        $response->assertSee('Log out');
        $response->assertSee('openProfileModal()', false);
        $response->assertSee('showAdminLogoutConfirm()', false);
    }

    public function test_admin_topbar_is_removed_and_profile_modal_is_present(): void
    {
        $admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('admin-topbar');
        $response->assertSee('profile-modal');
        $response->assertSee('confirm-admin-logout-modal');
    }

    public function test_admin_summary_stat_widget_renders_on_home_feed_only_for_admin_users(): void
    {
        $admin = User::create([
            'name'     => 'Kupkop Administrator',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Available Pup',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 yr',
            'gender'      => 'Male',
            'description' => 'Test',
            'status'      => 'Available',
            'posted_by'   => $user->name,
        ]);

        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Pending Kitty',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '2 yrs',
            'gender'      => 'Female',
            'description' => 'Test',
            'status'      => 'Pending',
            'posted_by'   => $user->name,
        ]);

        // 1. Admin view: renders summary stat cards and HIDES the "+" Create Post button
        $adminResponse = $this->actingAs($admin)->get(route('home'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('hero-admin-stats-widget');
        $adminResponse->assertSee('Pets listed');
        $adminResponse->assertSee('Available');
        $adminResponse->assertSee('Pending');
        $adminResponse->assertDontSee('btn-hero-create-post');
        $adminResponse->assertSee('Welcome, Kupkop Administrator!');

        // 2. Regular user view: does NOT see admin stats, DOES see "+" Create Post button
        $userResponse = $this->actingAs($user)->get(route('home'));
        $userResponse->assertStatus(200);
        $userResponse->assertDontSee('hero-admin-stats-widget');
        $userResponse->assertSee('btn-hero-create-post');
        $userResponse->assertSee('Welcome, Regular User!');

        // 3. Guest view: should NOT see the admin stat cards widget or create post button
        auth()->logout();
        $guestResponse = $this->get(route('home'));
        $guestResponse->assertStatus(200);
        $guestResponse->assertDontSee('hero-admin-stats-widget');
        $guestResponse->assertDontSee('Pets listed');
        $guestResponse->assertDontSee('btn-hero-create-post');
    }

    public function test_admin_navigation_bar_hides_home_tab_and_page_switcher(): void
    {
        $admin = User::create([
            'name'     => 'Kupkop Administrator',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        // 1. Admin navigation: Home tab is removed, page switcher is hidden, retains Admin Panel pill and user info
        $adminNav = $this->actingAs($admin)->get(route('home'));
        $adminNav->assertStatus(200);
        $adminNav->assertDontSee('<span class="nav-label">Home</span>', false);
        $adminNav->assertDontSee('nav-page-switcher');
        $adminNav->assertDontSee('<span class="nav-label">My Posts</span>', false);
        $adminNav->assertDontSee('<span class="nav-label">Messages</span>', false);
        $adminNav->assertDontSee('nav-msg-link');
        $adminNav->assertSee('Admin Panel');
        $adminNav->assertSee('nav-user-display');
        $adminNav->assertSee('Kupkop Administrator');
        $adminNav->assertDontSee('nav-user-btn');
        $adminNav->assertDontSee('title="Click to edit account details"', false);

        // 2. Regular user navigation: Home, My Posts, and Messages all visible in page switcher
        $userNav = $this->actingAs($user)->get(route('home'));
        $userNav->assertStatus(200);
        $userNav->assertSee('nav-page-switcher');
        $userNav->assertSee('<span class="nav-label">Home</span>', false);
        $userNav->assertSee('<span class="nav-label">My Posts</span>', false);
        $userNav->assertSee('<span class="nav-label">Messages</span>', false);
        $userNav->assertDontSee('Admin Panel');
        $userNav->assertSee('nav-user-btn');
        $userNav->assertSee('title="Click to edit account details"', false);
        $userNav->assertSee('openProfileModal()', false);
    }

    public function test_admin_is_restricted_from_my_posts_and_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name'     => 'Kupkop Administrator',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        // Admin attempting to visit /my-posts is redirected to /admin/dashboard with a warning
        $response = $this->actingAs($admin)->get('/my-posts');
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('warning');

        // Follow redirect to confirm warning alert is displayed
        $follow = $this->actingAs($admin)->get('/admin/dashboard');
        $follow->assertStatus(200);
        $follow->assertSee('Admin Dashboard');

        // Regular user can access /my-posts normally
        $userResponse = $this->actingAs($user)->get('/my-posts');
        $userResponse->assertStatus(200);
        $userResponse->assertSee('My Active Animal Posts');
    }

    public function test_admin_is_restricted_from_messages_and_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name'     => 'Kupkop Administrator',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $user = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);

        // Admin attempting to visit /messages is redirected to /admin/dashboard with a warning
        $response = $this->actingAs($admin)->get('/messages');
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('warning');

        // Admin making JSON request to /messages receives 403 Forbidden
        $jsonResponse = $this->actingAs($admin)->getJson('/messages');
        $jsonResponse->assertStatus(403);

        // Regular user can access /messages normally
        $userResponse = $this->actingAs($user)->get('/messages');
        $userResponse->assertStatus(200);
    }
}
