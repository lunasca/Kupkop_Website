<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_post_with_custom_location(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name' => 'Bantay',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 year',
            'gender' => 'Male',
            'location' => 'Zone 4, Phase 1, Package 3, Brgy. 176-F',
            'status' => 'Available',
            'description' => 'A very playful and friendly barangay rescue dog.',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('pets', [
            'name' => 'Bantay',
            'location' => 'Zone 4, Phase 1, Package 3, Brgy. 176-F',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_post_location(): void
    {
        $user = User::factory()->create();
        $pet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Mimi',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '6 months',
            'gender' => 'Female',
            'location' => 'Zone 1, Brgy. 176-F',
            'status' => 'Available',
            'description' => 'Sweet rescued kitten.',
        ]);

        $response = $this->actingAs($user)
            ->from(route('my-posts'))
            ->put(route('posts.update', $pet), [
                'name' => 'Mimi',
                'species' => 'Cat',
                'breed' => 'Puspin',
                'age' => '7 months',
                'gender' => 'Female',
                'location' => 'Zone 5, Phase 2, Brgy. 176-F',
                'status' => 'Available',
                'description' => 'Sweet rescued kitten growing up healthy.',
            ]);

        $response->assertRedirect(route('my-posts'));
        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'location' => 'Zone 5, Phase 2, Brgy. 176-F',
            'age' => '7 months',
        ]);
    }

    public function test_user_can_save_post_as_draft(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name' => 'Brownie Draft',
            'species' => 'Dog',
            'action' => 'draft',
        ]);

        $response->assertRedirect(route('my-posts'));
        $this->assertDatabaseHas('pets', [
            'name' => 'Brownie Draft',
            'status' => 'Draft',
            'user_id' => $user->id,
        ]);
    }

    public function test_my_posts_separates_drafts_from_active_posts(): void
    {
        $user = User::factory()->create();

        $activePet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Active Doggo',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '2 years',
            'gender' => 'Male',
            'location' => 'Zone 1, Brgy. 176-F',
            'status' => 'Available',
            'description' => 'Active and playful.',
        ]);

        $draftPet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Draft Kitty',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '3 months',
            'gender' => 'Female',
            'location' => 'Zone 2, Brgy. 176-F',
            'status' => 'Draft',
            'description' => 'Draft kitten.',
        ]);

        $response = $this->actingAs($user)->get(route('my-posts'));
        $response->assertOk();
        $response->assertViewHas('myPets', function ($myPets) use ($activePet, $draftPet) {
            return $myPets->contains($activePet) && !$myPets->contains($draftPet);
        });
        $response->assertViewHas('draftPets', function ($draftPets) use ($draftPet, $activePet) {
            return $draftPets->contains($draftPet) && !$draftPets->contains($activePet);
        });
        $response->assertSee('Drafts');
    }

    public function test_user_can_delete_draft(): void
    {
        $user = User::factory()->create();

        $draftPet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Unwanted Draft',
            'species' => 'Dog',
            'age' => '1 year',
            'gender' => 'Male',
            'location' => 'Brgy. 176-F',
            'status' => 'Draft',
            'description' => 'Temporary draft',
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $draftPet));
        $response->assertRedirect();
        $this->assertDatabaseMissing('pets', [
            'id' => $draftPet->id,
        ]);
    }

    public function test_user_can_delete_active_post(): void
    {
        $user = User::factory()->create();

        $activePet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Bantay The Dog',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '2 years',
            'gender' => 'Male',
            'location' => 'Zone 4, Brgy. 176-F',
            'status' => 'Available',
            'description' => 'Ready for a loving forever home.',
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $activePet));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Post "Bantay The Dog" has been deleted successfully.');
        $this->assertDatabaseMissing('pets', [
            'id' => $activePet->id,
        ]);
    }

    public function test_user_cannot_delete_other_user_post(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $pet = Pet::create([
            'user_id' => $owner->id,
            'name' => 'Protected Dog',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 years',
            'gender' => 'Male',
            'location' => 'Zone 2, Brgy. 176-F',
            'status' => 'Available',
            'description' => 'Belongs to owner.',
        ]);

        $response = $this->actingAs($otherUser)->delete(route('posts.destroy', $pet));
        $response->assertForbidden();
        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
        ]);
    }

    public function test_create_post_modal_location_field_is_empty_by_default_and_has_placeholder(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('my-posts'));
        $response->assertOk();
        $response->assertSee('placeholder="e.g., Package 6, Brgy. 176-F"', false);
        $response->assertDontSee('value="Brgy. 176-F, Caloocan City"', false);
    }
}
