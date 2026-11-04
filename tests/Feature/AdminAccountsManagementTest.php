<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccountsManagementTest extends TestCase
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
            'status'   => 'active',
        ]);

        $this->regularUser = User::create([
            'name'     => 'Regular User',
            'email'    => 'user@kupkop.test',
            'password' => bcrypt('password123'),
            'is_admin' => false,
            'status'   => 'active',
        ]);
    }

    // -----------------------------------------------------------------------
    // Access Control
    // -----------------------------------------------------------------------

    public function test_non_admin_users_receive_403_on_accounts_page(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin/accounts');
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_from_accounts_page(): void
    {
        $response = $this->get('/admin/accounts');
        $response->assertRedirect();
    }

    public function test_non_admin_receives_403_on_block_endpoint(): void
    {
        $target = User::create([
            'name' => 'Target', 'email' => 'target@test.com',
            'password' => bcrypt('pass'), 'is_admin' => false, 'status' => 'active',
        ]);
        $response = $this->actingAs($this->regularUser)->patch("/admin/accounts/{$target->id}/block");
        $response->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // Accounts Page Rendering
    // -----------------------------------------------------------------------

    public function test_admin_can_view_accounts_page_with_tabs_and_counts(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/accounts');
        $response->assertStatus(200);
        $response->assertSee('User accounts');
        $response->assertSee('Archive');
        $response->assertSee('Regular User');
        $response->assertSee('user@kupkop.test');
        // Admin should NOT appear in the table
        $response->assertDontSee('<div class="pet-id">admin@kupkop.test</div>', false);
    }

    public function test_admin_account_is_never_listed_in_accounts_table(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/accounts');
        $response->assertStatus(200);
        // Admin's own email should NOT appear in the user table
        $response->assertDontSee('<div class="pet-id">admin@kupkop.test</div>', false);
    }

    public function test_search_filter_works_by_name(): void
    {
        User::create([
            'name' => 'Unique Name XYZ', 'email' => 'uniquexyz@test.com',
            'password' => bcrypt('pass'), 'is_admin' => false, 'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/accounts?search=Unique+Name+XYZ');
        $response->assertStatus(200);
        $response->assertSee('Unique Name XYZ');
        $response->assertDontSee('Regular User');
    }

    public function test_status_filter_shows_only_blocked_users(): void
    {
        $blockedUser = User::create([
            'name' => 'Blocked Person', 'email' => 'blocked@test.com',
            'password' => bcrypt('pass'), 'is_admin' => false, 'status' => 'blocked',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/accounts?status=blocked');
        $response->assertStatus(200);
        $response->assertSee('Blocked Person');
        $response->assertDontSee('Regular User');
    }

    // -----------------------------------------------------------------------
    // Block / Unblock
    // -----------------------------------------------------------------------

    public function test_admin_can_block_a_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/block");

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'         => $this->regularUser->id,
            'status'     => 'blocked',
            'is_blocked' => true,
        ]);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('account_restricted');
        $this->assertGuest();
    }

    public function test_admin_can_unblock_a_user(): void
    {
        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/unblock");

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'         => $this->regularUser->id,
            'status'     => 'active',
            'is_blocked' => false,
        ]);
    }

    public function test_unblocked_user_can_login_again(): void
    {
        $this->regularUser->update(['status' => 'active', 'is_blocked' => false]);

        $response = $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    public function test_admin_cannot_block_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->admin->id}/block");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Admin should still be active
        $this->assertDatabaseHas('users', [
            'id'     => $this->admin->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_block_another_admin(): void
    {
        $anotherAdmin = User::create([
            'name' => 'Second Admin', 'email' => 'admin2@test.com',
            'password' => bcrypt('pass'), 'is_admin' => true, 'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$anotherAdmin->id}/block");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id'     => $anotherAdmin->id,
            'status' => 'active',
        ]);
    }

    public function test_blocking_updates_dashboard_blocked_accounts_count(): void
    {
        // No blocked users initially
        $dashBefore = $this->actingAs($this->admin)->get('/admin/dashboard');
        $dashBefore->assertStatus(200);
        $dashBefore->assertDontSee('blocked account');

        // Block the regular user
        $this->actingAs($this->admin)->patch("/admin/accounts/{$this->regularUser->id}/block");

        // Dashboard should now show 1 blocked account
        $dashAfter = $this->actingAs($this->admin)->get('/admin/dashboard');
        $dashAfter->assertSee('1 blocked account');
    }

    public function test_dashboard_open_button_navigates_to_blocked_accounts(): void
    {
        // Block someone so the item appears
        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        // The "Open" link should point to accounts?status=blocked
        $response->assertSee(route('admin.accounts', ['status' => 'blocked']), false);
    }

    // -----------------------------------------------------------------------
    // Remove (Archive) / Restore
    // -----------------------------------------------------------------------

    public function test_admin_can_remove_account_moves_to_archive_tab(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/archive");

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'     => $this->regularUser->id,
            'status' => 'blocked',
        ]);

        $this->assertNotNull(
            User::find($this->regularUser->id)->archived_at,
            'archived_at should be set after removal.'
        );
    }

    public function test_removed_account_appears_in_archive_tab(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked']);

        $response = $this->actingAs($this->admin)->get('/admin/accounts?tab=archive');
        $response->assertStatus(200);
        $response->assertSee('Regular User');
        $response->assertSee('Archived');
    }

    public function test_removed_account_does_not_appear_in_user_accounts_tab(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked']);

        $response = $this->actingAs($this->admin)->get('/admin/accounts?tab=users');
        $response->assertStatus(200);
        $response->assertDontSee('Regular User');
    }

    public function test_archived_user_cannot_login(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked', 'is_blocked' => true]);

        $response = $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('account_restricted');
        $this->assertGuest();
    }

    public function test_admin_can_restore_archived_account(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/restore");

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'         => $this->regularUser->id,
            'status'     => 'active',
            'is_blocked' => false,
        ]);

        $this->assertNull(User::find($this->regularUser->id)->archived_at);
    }

    public function test_admin_cannot_remove_their_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->admin->id}/archive");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertNull(User::find($this->admin->id)->archived_at);
    }

    // -----------------------------------------------------------------------
    // Permanent Deletion
    // -----------------------------------------------------------------------

    public function test_admin_can_permanently_delete_archived_user(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked']);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/accounts/{$this->regularUser->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $this->regularUser->id]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete("/admin/accounts/{$this->admin->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    // -----------------------------------------------------------------------
    // Blocked user's posts hidden from public feed
    // -----------------------------------------------------------------------

    public function test_blocked_user_posts_are_hidden_from_public_feed(): void
    {
        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'VisiblePet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'description' => 'Should be hidden when owner blocked',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        // Verify pet appears before blocking
        $before = $this->get('/');
        $before->assertSee('VisiblePet');

        // Block the user
        $this->actingAs($this->admin)->patch("/admin/accounts/{$this->regularUser->id}/block");

        // Pet should now be hidden
        $after = $this->get('/');
        $after->assertDontSee('VisiblePet');
    }

    public function test_archived_user_posts_hidden_from_public_feed(): void
    {
        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'ArchivedOwnerPet',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '2 years',
            'gender'      => 'Female',
            'description' => 'Should be hidden when owner is archived',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        // Before archiving — visible
        $before = $this->get('/');
        $before->assertSee('ArchivedOwnerPet');

        // Archive the user
        $this->actingAs($this->admin)->patch("/admin/accounts/{$this->regularUser->id}/archive");

        // After archiving — hidden
        $after = $this->get('/');
        $after->assertDontSee('ArchivedOwnerPet');
    }

    public function test_blocked_user_cannot_create_post(): void
    {
        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->regularUser)->post('/posts', [
            'name'        => 'Blocked Puppy',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 yr',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'description' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_blocked_user_cannot_submit_adoption_request(): void
    {
        $pet = Pet::create([
            'user_id'     => $this->admin->id,
            'name'        => 'AdminPet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'description' => 'Cute dog',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->regularUser)->post('/adoption-requests', [
            'pet_id'         => $pet->id,
            'applicant_name' => 'Blocked Person',
            'contact_number' => '09123456789',
            'address'        => 'Block 1 Lot 2, Brgy. 176-F',
            'message'        => 'I want this pet',
        ]);

        $response->assertStatus(403);
    }

    public function test_blocked_user_cannot_send_messages(): void
    {
        $this->regularUser->update(['status' => 'blocked', 'is_blocked' => true]);

        $response = $this->actingAs($this->regularUser)->postJson('/messages', [
            'receiver_id' => $this->admin->id,
            'message'     => 'Hello admin',
        ]);

        $response->assertStatus(403);
    }

    public function test_restored_user_posts_reappear_on_public_feed(): void
    {
        $this->regularUser->update(['archived_at' => now(), 'status' => 'blocked', 'is_blocked' => true]);

        Pet::create([
            'user_id'     => $this->regularUser->id,
            'name'        => 'RestoredPet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '3 years',
            'gender'      => 'Male',
            'description' => 'Should reappear after owner restore',
            'status'      => 'Available',
            'is_verified' => true,
        ]);

        // Pet should be hidden while user is archived
        $before = $this->get('/');
        $before->assertDontSee('RestoredPet');

        // Restore the user
        $this->actingAs($this->admin)->patch("/admin/accounts/{$this->regularUser->id}/restore");

        // Pet should be visible again
        $after = $this->get('/');
        $after->assertSee('RestoredPet');
    }

    // -----------------------------------------------------------------------
    // Notification & Reason System
    // -----------------------------------------------------------------------

    public function test_block_modal_ui_renders_reason_textarea(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/accounts');
        $response->assertStatus(200);
        $response->assertSee('block-reason', false);
        $response->assertSee('Reason for blocking', false);
        $response->assertSee('Violation of community rules', false);
    }

    public function test_block_stores_reason_in_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/block", [
                'reason' => 'Posted inappropriate content repeatedly.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'           => $this->regularUser->id,
            'status'       => 'blocked',
            'block_reason' => 'Posted inappropriate content repeatedly.',
        ]);
        $this->assertNotNull(User::find($this->regularUser->id)->blocked_at);
    }

    public function test_block_uses_default_reason_when_none_provided(): void
    {
        $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/block");

        $user = User::find($this->regularUser->id);
        $this->assertNotNull($user->block_reason);
        $this->assertSame('Violation of community guidelines.', $user->block_reason);
    }

    public function test_archive_stores_reason_in_database(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/archive", [
                'reason' => 'Fraudulent adoption listings.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id'           => $this->regularUser->id,
            'block_reason' => 'Fraudulent adoption listings.',
        ]);
    }

    public function test_unblock_clears_block_reason_and_blocked_at(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Some reason',
            'blocked_at'   => now(),
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/unblock");

        $user = User::find($this->regularUser->id);
        $this->assertNull($user->block_reason);
        $this->assertNull($user->blocked_at);
    }

    public function test_restore_clears_block_reason_and_blocked_at(): void
    {
        $this->regularUser->update([
            'archived_at'  => now(),
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Archived reason',
            'blocked_at'   => now(),
        ]);

        $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/restore");

        $user = User::find($this->regularUser->id);
        $this->assertNull($user->block_reason);
        $this->assertNull($user->blocked_at);
    }

    public function test_notification_email_is_sent_when_user_is_blocked(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/block", [
                'reason' => 'Spam activity detected.',
            ]);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\AccountRestrictionMail::class,
            function ($mail) {
                return $mail->hasTo($this->regularUser->email)
                    && $mail->action === 'blocked'
                    && $mail->reason === 'Spam activity detected.';
            }
        );
    }

    public function test_notification_email_is_sent_when_user_is_archived(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $this->actingAs($this->admin)
            ->patch("/admin/accounts/{$this->regularUser->id}/archive", [
                'reason' => 'Repeated violations.',
            ]);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\AccountRestrictionMail::class,
            function ($mail) {
                return $mail->hasTo($this->regularUser->email)
                    && $mail->action === 'archived';
            }
        );
    }

    public function test_blocked_login_shows_account_restricted_error(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Test restriction reason.',
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('account_restricted');
        $this->assertGuest();
    }

    public function test_login_view_shows_restriction_banner_for_blocked_user(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Posted spam content.',
        ]);

        // Attempt login (which will fail and redirect back with errors)
        $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        // Follow the redirect to the login page
        $response = $this->get('/login');
        $response->assertStatus(200);
        // The dedicated restriction modal elements should be present
        $response->assertSee('account-restricted-modal', false);
        $response->assertSee('Account Access Restricted', false);
        $response->assertSee('Your account has been blocked by the Barangay Kupkop Administrator.', false);
        $response->assertSee('Posted spam content.', false);
        $response->assertSee('Barangay 176-F', false);
        $response->assertSee('Back to Login', false);
    }

    public function test_login_intercepts_removed_user_with_account_suspended_notice(): void
    {
        $this->regularUser->update([
            'status'       => 'removed',
            'is_blocked'   => true,
            'block_reason' => 'Violation of community adoption guidelines',
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('is_account_restricted', true);
        $response->assertSessionHas('account_blocked', true);
        $response->assertSessionHas('restriction_title', 'Account Suspended');
        $response->assertSessionHas('restriction_message', 'Your account has been removed by the Barangay Kupkop Administrator.');
        $response->assertSessionHas('block_reason', 'Violation of community adoption guidelines');
        $this->assertGuest();
    }

    public function test_auth_modal_renders_restriction_alert_banner_on_login_attempt(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Inappropriate pet post behavior.',
        ]);

        // Attempt login
        $this->post('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        // Visit home page where auth modals are mounted
        $response = $this->get('/');
        $response->assertStatus(200);
        // Dedicated account restriction modal is present with required details
        $response->assertSee('id="account-restricted-modal"', false);
        $response->assertSee('Account Access Restricted', false);
        $response->assertSee('Inappropriate pet post behavior.', false);
        $response->assertSee('Barangay 176-F Support', false);
        $response->assertSee('Back to Login', false);
    }

    public function test_json_login_attempt_returns_403_restricted_status(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Spamming adoption messages.',
        ]);

        $response = $this->postJson('/login', [
            'email'    => 'user@kupkop.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'            => 'restricted',
            'restriction_title' => 'Account Access Restricted',
            'block_reason'      => 'Spamming adoption messages.',
        ]);
        $this->assertGuest();
    }

    public function test_admin_can_view_blocked_accounts_tab_with_dynamic_badge_counter(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Suspicious adoption activity',
            'blocked_at'   => now(),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/accounts?tab=blocked');
        $response->assertStatus(200);
        $response->assertSee('Blocked Accounts');
        $response->assertSee('blocked-count-badge');
        $response->assertSee('Suspicious adoption activity');
        $response->assertSee('Regular User');
        $response->assertSee('Account Role');
        $response->assertSee('Date Blocked');
        $response->assertSee('Reason for Block');
        $response->assertSee('Unblock Account');
    }

    public function test_admin_can_unblock_user_via_ajax_and_receive_updated_counts(): void
    {
        $this->regularUser->update([
            'status'       => 'blocked',
            'is_blocked'   => true,
            'block_reason' => 'Some violation',
            'blocked_at'   => now(),
        ]);

        $response = $this->actingAs($this->admin)->patchJson("/admin/accounts/{$this->regularUser->id}/unblock");

        $response->assertStatus(200);
        $response->assertJson([
            'success'       => true,
            'user_id'       => $this->regularUser->id,
            'blocked_count' => 0,
        ]);

        $this->assertDatabaseHas('users', [
            'id'         => $this->regularUser->id,
            'status'     => 'active',
            'is_blocked' => false,
        ]);
    }

    public function test_unblock_modal_contains_restore_access_confirmation_message(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/accounts');
        $response->assertStatus(200);
        $response->assertSee('Are you sure you want to restore access for', false);
        $response->assertSee('Restore access', false);
    }
}
