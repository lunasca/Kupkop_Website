<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->put(route('profile.update'), [
            'name'  => 'New Name',
            'email' => 'newemail@example.com',
        ]);

        $response->assertRedirect(route('home'));
    }

    public function test_user_can_update_name_and_email(): void
    {
        $user = User::factory()->create([
            'name'  => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_take_another_users_email(): void
    {
        $user1 = User::factory()->create(['email' => 'first@example.com']);
        $user2 = User::factory()->create(['email' => 'second@example.com']);

        $response = $this->actingAs($user2)->put(route('profile.update'), [
            'name'  => 'Second User',
            'email' => 'first@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseHas('users', [
            'id'    => $user2->id,
            'email' => 'second@example.com',
        ]);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'current_password'      => 'oldpassword123',
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword456', $user->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name'                  => $user->name,
            'email'                 => $user->email,
            'current_password'      => 'wrongpassword',
            'password'              => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors('current_password');

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_updating_name_updates_posted_by_on_user_pets(): void
    {
        $user = User::factory()->create(['name' => 'Original Poster']);

        $pet = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Lucky',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Friendly dog',
            'posted_by'   => 'Original Poster',
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name'  => 'New Poster Name',
            'email' => $user->email,
        ]);

        $pet->refresh();
        $this->assertEquals('New Poster Name', $pet->posted_by);
    }
}
