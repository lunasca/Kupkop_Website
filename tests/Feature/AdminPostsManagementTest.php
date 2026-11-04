<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPostsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'     => 'Shelter Admin',
            'email'    => 'admin@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $this->regularUser = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
        ]);
    }

    public function test_non_admin_users_receive_403_forbidden_on_admin_posts_endpoints(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Milo',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'description' => 'Test dog',
            'status'      => 'Available',
            'is_verified' => false,
        ]);

        // Guest access redirects (to home, per app bootstrap)
        $this->get('/admin/posts')->assertRedirect('/');

        // Regular user receives 403 Forbidden
        $this->actingAs($this->regularUser)->get('/admin/posts')->assertStatus(403);
        $this->actingAs($this->regularUser)->patch("/admin/posts/{$pet->id}/verify")->assertStatus(403);
        $this->actingAs($this->regularUser)->patch("/admin/posts/{$pet->id}/unverify")->assertStatus(403);
        $this->actingAs($this->regularUser)->patch("/admin/posts/{$pet->id}/archive")->assertStatus(403);
        $this->actingAs($this->regularUser)->patch("/admin/posts/{$pet->id}/restore")->assertStatus(403);
        $this->actingAs($this->regularUser)->delete("/admin/posts/{$pet->id}")->assertStatus(403);
    }

    public function test_admin_can_view_all_active_posts_and_badge_counts(): void
    {
        $activePet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Alexa',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years old',
            'gender'      => 'Female',
            'description' => 'Friendly dog',
            'status'      => 'Available',
            'is_verified' => false,
            'posted_by'   => 'Margarette Karylle',
        ]);

        $archivedPet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Old Pet',
            'species'     => 'Cat',
            'breed'       => 'Persian',
            'age'         => '3 years',
            'gender'      => 'Male',
            'description' => 'Archived cat',
            'status'      => 'Available',
            'is_verified' => true,
            'archived_at' => now(),
            'posted_by'   => 'John Doe',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/posts');
        $response->assertStatus(200);
        $response->assertSee('All posts');
        $response->assertSee('Archive');
        // Badge counts
        $response->assertSee('Alexa');
        $response->assertDontSee('Old Pet');
        // Distinct badges
        $response->assertSee('Unverified');
        $response->assertSee('Available');
        $response->assertSee('View & verify', false);
        $response->assertSee('Archive');
        $response->assertSee('Remove');
    }

    public function test_verifying_a_post_updates_status_badge_and_dashboard_count(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Oreo',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '8 months',
            'gender'      => 'Male',
            'description' => 'Playful cat',
            'status'      => 'Available',
            'is_verified' => false,
        ]);

        // Before: dashboard shows 1 post to verify
        $dashBefore = $this->actingAs($this->admin)->get('/admin/dashboard');
        $dashBefore->assertStatus(200);
        $dashBefore->assertSee('1 post to verify');

        // Verify post
        $response = $this->actingAs($this->admin)->patch("/admin/posts/{$pet->id}/verify");
        $response->assertRedirect();

        $this->assertDatabaseHas('pets', [
            'id'          => $pet->id,
            'is_verified' => true,
        ]);

        // After: dashboard shows 0 posts to verify -> empty state / all verified
        $dashAfter = $this->actingAs($this->admin)->get('/admin/dashboard');
        $dashAfter->assertStatus(200);
        $dashAfter->assertSee('All posts are verified');
        $dashAfter->assertDontSee('1 post to verify');

        // Admin posts table shows Verified badge
        $postsResponse = $this->actingAs($this->admin)->get('/admin/posts');
        $postsResponse->assertStatus(200);
        $postsResponse->assertSee('Verified');
    }

    public function test_admin_can_unverify_a_verified_post(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Snow',
            'species'     => 'Cat',
            'breed'       => 'Persian',
            'age'         => '1 year',
            'gender'      => 'Female',
            'description' => 'Fluffy cat',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch("/admin/posts/{$pet->id}/unverify");
        $response->assertRedirect();

        $this->assertDatabaseHas('pets', [
            'id'          => $pet->id,
            'is_verified' => false,
        ]);
    }

    public function test_archiving_a_post_moves_it_to_archive_and_hides_from_public_feed(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Brownie',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'description' => 'Rescue pup',
            'status'      => 'Available',
            'posted_by'   => 'Margarette Karylle',
        ]);

        // Public feed displays it when published and active (is_verified is an admin-review marker, not a visibility gate)
        $this->get('/')->assertStatus(200)->assertSee('Brownie');

        // Admin archives the post
        $archiveResponse = $this->actingAs($this->admin)->patch("/admin/posts/{$pet->id}/archive");
        $archiveResponse->assertRedirect();

        $pet->refresh();
        $this->assertNotNull($pet->archived_at);

        // Public feed now hides it
        $this->get('/')->assertStatus(200)->assertDontSee('Brownie');

        // "All posts" tab does not show it
        $allTab = $this->actingAs($this->admin)->get('/admin/posts?tab=all');
        $allTab->assertStatus(200)->assertDontSee('Brownie');

        // "Archive" tab displays it with Archived badge, Restore, and Delete permanently buttons
        $archiveTab = $this->actingAs($this->admin)->get('/admin/posts?tab=archive');
        $archiveTab->assertStatus(200);
        $archiveTab->assertSee('Brownie');
        $archiveTab->assertSee('Archived');
        $archiveTab->assertSee('Restore');
        $archiveTab->assertSee('Delete permanently');
    }

    public function test_restoring_an_archived_post_returns_it_to_all_posts_tab(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Yorkie',
            'species'     => 'Dog',
            'breed'       => 'Poodle mix',
            'age'         => '5 months',
            'gender'      => 'Male',
            'description' => 'Playful puppy',
            'status'      => 'Available',
            'is_verified' => true,
            'archived_at' => now(),
        ]);

        $restoreResponse = $this->actingAs($this->admin)->patch("/admin/posts/{$pet->id}/restore");
        $restoreResponse->assertRedirect();

        $pet->refresh();
        $this->assertNull($pet->archived_at);

        // Now appears back in "All posts" tab
        $allTab = $this->actingAs($this->admin)->get('/admin/posts?tab=all');
        $allTab->assertStatus(200)->assertSee('Yorkie');
    }

    public function test_permanent_deletion_removes_entry_permanently_from_database(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Cheap Persian',
            'species'     => 'Cat',
            'breed'       => 'Persian',
            'age'         => '1 year',
            'gender'      => 'Female',
            'description' => 'Suspicious scam post',
            'status'      => 'Available',
            'is_verified' => false,
            'archived_at' => now(),
        ]);

        $deleteResponse = $this->actingAs($this->admin)->delete("/admin/posts/{$pet->id}");
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }

    public function test_review_button_from_dashboard_properly_navigates_with_unverified_filter(): void
    {
        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Unverified Puppy',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '3 months',
            'gender'      => 'Male',
            'description' => 'Needs review',
            'status'      => 'Available',
            'is_verified' => false,
        ]);

        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Verified Kitty',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '2 years',
            'gender'      => 'Female',
            'description' => 'Already verified',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        // Dashboard renders review button linking to /admin/posts?verification=unverified
        $dashResponse = $this->actingAs($this->admin)->get('/admin/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('/admin/posts?verification=unverified');

        // Visiting that URL pre-selects unverified and filters posts accordingly
        $postsResponse = $this->actingAs($this->admin)->get('/admin/posts?verification=unverified');
        $postsResponse->assertStatus(200);
        $postsResponse->assertSee('Unverified Puppy');
        $postsResponse->assertDontSee('Verified Kitty');
    }

    public function test_admin_posts_search_and_filters(): void
    {
        $dog = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Takeshi',
            'species'     => 'Dog',
            'breed'       => 'Shih tzu',
            'age'         => '2 years',
            'gender'      => 'Male',
            'description' => 'Cute Shih tzu',
            'status'      => 'Pending',
            'is_verified' => true,
            'posted_by'   => 'Lawrence Dullo',
        ]);

        $cat = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Orange',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '5 years',
            'gender'      => 'Female',
            'description' => 'Orange cat',
            'status'      => 'Available',
            'is_verified' => false,
            'posted_by'   => 'Maria Clara',
        ]);

        // Search by pet name
        $res1 = $this->actingAs($this->admin)->get('/admin/posts?search=Takeshi');
        $res1->assertSee('Takeshi')->assertDontSee('Orange');

        // Search by owner name
        $res2 = $this->actingAs($this->admin)->get('/admin/posts?search=Lawrence');
        $res2->assertSee('Takeshi')->assertDontSee('Orange');

        // Filter by Species
        $res3 = $this->actingAs($this->admin)->get('/admin/posts?species=Cat');
        $res3->assertSee('Orange')->assertDontSee('Takeshi');

        // Filter by Verification
        $res4 = $this->actingAs($this->admin)->get('/admin/posts?verification=unverified');
        $res4->assertSee('Orange')->assertDontSee('Takeshi');
    }

    public function test_published_posts_appear_on_public_feed_regardless_of_verification_status(): void
    {
        // Unverified post: visible on the public feed immediately after publishing.
        // is_verified is an admin-review marker only — it does NOT gate public visibility.
        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Unverified But Visible Pet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'description' => 'Pending admin review but still publicly listed',
            'status'      => 'Available',
            'is_verified' => false,
        ]);

        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'Verified And Visible Pet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Female',
            'description' => 'Already reviewed by admin',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        // Both verified and unverified published posts should appear on the public feed
        $response->assertSee('Unverified But Visible Pet');
        $response->assertSee('Verified And Visible Pet');
    }

    public function test_admin_posts_view_renders_multi_photo_badge_and_fullscreen_carousel_modal(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'MultiPhotoPet',
            'species'     => 'Dog',
            'breed'       => 'Golden Retriever',
            'age'         => '2 years',
            'gender'      => 'Male',
            'description' => 'A friendly dog with multiple photos',
            'status'      => 'Available',
            'is_verified' => false,
            'image_path'  => json_encode(['pets/photo1.jpg', 'pets/photo2.jpg', 'pets/photo3.jpg']),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/posts');
        $response->assertStatus(200);

        // Pet table renders +2 multi photo badge
        $response->assertSee('+2');
        $response->assertSee('3 photos uploaded');

        // Modal contains thumbnail strip and carousel elements
        $response->assertSee('vm-gallery-thumbnails');
        $response->assertSee('admin-fullscreen-image-modal');
        $response->assertSee('admin-pet-fullscreen-img');
        $response->assertSee('admin-fs-carousel-thumbnails-strip');
    }
}
