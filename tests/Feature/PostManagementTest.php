<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeImage(string $name = 'photo.png'): UploadedFile
    {
        $png1x1 = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return UploadedFile::fake()->createWithContent($name, $png1x1);
    }

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
            'pet_images' => [$this->fakeImage('bantay.png')],
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

    public function test_user_can_archive_active_post(): void
    {
        $user = User::factory()->create();
        $pet  = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Archivable Dog',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Zone 1, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'A post to be archived.',
        ]);

        $response = $this->actingAs($user)->patch(route('posts.archive', $pet));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNotNull($pet->fresh()->archived_at);
    }

    public function test_user_cannot_archive_another_users_post(): void
    {
        $owner     = User::factory()->create();
        $otherUser = User::factory()->create();
        $pet       = Pet::create([
            'user_id'     => $owner->id,
            'name'        => 'Protected Cat',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '1 year',
            'gender'      => 'Female',
            'location'    => 'Zone 3, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Owner\'s pet.',
        ]);

        $response = $this->actingAs($otherUser)->patch(route('posts.archive', $pet));

        $response->assertForbidden();
        $this->assertNull($pet->fresh()->archived_at);
    }

    public function test_user_can_restore_archived_post(): void
    {
        $user = User::factory()->create();
        $pet  = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Restorable Dog',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '3 years',
            'gender'      => 'Male',
            'location'    => 'Zone 2, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Post to be restored.',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($user)->patch(route('posts.restore', $pet));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNull($pet->fresh()->archived_at);
    }

    public function test_user_can_permanently_delete_archived_post(): void
    {
        $user = User::factory()->create();
        $pet  = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Deletable Archived Dog',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'location'    => 'Zone 5, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Archived post to be permanently deleted.',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('posts.destroy', $pet));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }

    public function test_archived_posts_tab_renders_on_my_posts_page(): void
    {
        $user = User::factory()->create();
        $archivedPet = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Visible Archived',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Female',
            'location'    => 'Zone 7, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Should appear in archived tab.',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('my-posts'));

        $response->assertOk();
        $response->assertSee('tab-panel-archived', false);
        $response->assertSee('Archived Posts', false);
        $response->assertSee('Visible Archived', false);
    }

    public function test_user_can_mark_active_post_as_adopted(): void
    {
        $user = User::factory()->create();
        $pet  = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Fluffy',
            'species'     => 'Cat',
            'breed'       => 'Puspin',
            'age'         => '1 year',
            'gender'      => 'Female',
            'location'    => 'Zone 1, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Test cat.',
        ]);

        $response = $this->actingAs($user)->patch(route('posts.adopt', $pet));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Adopted', $pet->fresh()->status);
    }

    public function test_user_can_revert_adopted_post_back_to_available(): void
    {
        $user = User::factory()->create();
        $pet  = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Brownie',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Zone 2, Brgy. 176-F',
            'status'      => 'Adopted',
            'description' => 'Already adopted — should be revertible.',
        ]);

        $response = $this->actingAs($user)->patch(route('posts.adopt', $pet));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Available', $pet->fresh()->status);
    }

    public function test_user_cannot_toggle_adopted_status_on_another_users_post(): void
    {
        $owner     = User::factory()->create();
        $otherUser = User::factory()->create();
        $pet       = Pet::create([
            'user_id'     => $owner->id,
            'name'        => 'Guard Dog',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '4 years',
            'gender'      => 'Male',
            'location'    => 'Zone 3, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Owner only.',
        ]);

        $response = $this->actingAs($otherUser)->patch(route('posts.adopt', $pet));

        $response->assertForbidden();
        $this->assertEquals('Available', $pet->fresh()->status);
    }

    public function test_animal_name_cannot_exceed_30_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'                 => str_repeat('A', 31),
            'species'              => 'Dog',
            'age'                  => '1 year',
            'gender'               => 'Male',
            'location'             => 'Zone 1, Brgy. 176-F',
            'status'               => 'Available',
            'description'          => 'Short valid description.',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('pets', [
            'description' => 'Short valid description.',
        ]);
    }

    public function test_description_cannot_exceed_300_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'                 => 'Bantay',
            'species'              => 'Dog',
            'age'                  => '1 year',
            'gender'               => 'Male',
            'location'             => 'Zone 1, Brgy. 176-F',
            'status'               => 'Available',
            'description'          => str_repeat('This is a very long description that exceeds the limit. ', 10),
        ]);

        $response->assertSessionHasErrors('description');
        $this->assertDatabaseMissing('pets', [
            'name' => 'Bantay',
        ]);
    }

    public function test_create_post_with_standardized_dropdown_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'                 => 'Luna',
            'species'              => 'Cat',
            'age'                  => '8 months',
            'gender'               => 'Female',
            'location'             => 'Zone 4, Brgy. 176-F',
            'vaccination_status'   => 'Up to date / Fully Vaccinated',
            'deworming_status'     => 'Dewormed (Up to date)',
            'status'               => 'Available',
            'description'          => 'A loving rescue cat looking for a home.',
            'pet_images'           => [$this->fakeImage('luna.png')],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('pets', [
            'name'               => 'Luna',
            'vaccination_status' => 'Up to date / Fully Vaccinated',
            'deworming_status'   => 'Dewormed (Up to date)',
        ]);
    }

    public function test_create_post_modal_renders_character_counters_and_dropdown_styling(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('my-posts'));

        $response->assertOk();
        // Check character counters
        $response->assertSee('id="create-name-counter"', false);
        $response->assertSee('id="create-description-counter"', false);
        $response->assertSee('maxlength="30"', false);
        $response->assertSee('maxlength="300"', false);
        // Check dropdown selects and styling wrapper
        $response->assertSee('class="form-select-styled"', false);
        $response->assertSee('class="custom-select-field-wrap"', false);
        $response->assertSee('id="vaccination_status"', false);
        $response->assertSee('id="deworming_status"', false);
        $response->assertSee('<option value="Up to date / Fully Vaccinated"', false);
        $response->assertSee('<option value="Dewormed (Up to date)"', false);
    }

    public function test_pet_name_is_automatically_capitalized_to_title_case(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'max power',
            'species'     => 'Dog',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Zone 1, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'A sweet dog.',
            'pet_images'  => [$this->fakeImage('max.png')],
        ]);

        $response->assertRedirect(route('home'));

        $pet = Pet::where('location', 'Zone 1, Brgy. 176-F')->first();
        $this->assertNotNull($pet);
        $this->assertEquals('Max Power', $pet->name);

        // Also test model attribute accessor on existing lowercase value
        $pet->name = 'yorkie';
        $this->assertEquals('Yorkie', $pet->name);
    }

    public function test_pet_description_is_automatically_capitalized_to_sentence_case(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'Brownie',
            'species'     => 'Dog',
            'age'         => '1 year',
            'gender'      => 'Male',
            'location'    => 'Zone 3, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'rescued near the market. loves playing with children! very gentle? yes indeed.',
            'pet_images'  => [$this->fakeImage('brownie.png')],
        ]);

        $response->assertRedirect(route('home'));

        $pet = Pet::where('name', 'Brownie')->first();
        $this->assertNotNull($pet);
        $this->assertEquals(
            'Rescued near the market. Loves playing with children! Very gentle? Yes indeed.',
            $pet->description
        );
    }

    public function test_home_page_and_modal_render_proper_structure_for_text_wrapping(): void
    {
        $user = User::factory()->create();
        Pet::create([
            'user_id'     => $user->id,
            'name'        => 'VeryLongPetNameWithoutSpacesToTestWrapping',
            'species'     => 'Dog',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Zone 2, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'VeryLongStoryWithoutSpacesToTestThatItWrapsProperlyInsideTheModalSectionFourStoryBoxAndDoesNotOverflow',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="modal-pet-name"', false);
        $response->assertSee('class="pet-modal-title"', false);
        $response->assertSee('id="modal-spec-story"', false);
        $response->assertSee('class="story-display-text"', false);
    }

    public function test_age_dropdown_renders_on_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('my-posts'));

        $response->assertOk();
        // Age field is now a select dropdown
        $response->assertSee('id="age"', false);
        $response->assertSee('name="age"', false);
        $response->assertSee('<select', false);
        // Standard option labels present
        $response->assertSee('1 month', false);
        $response->assertSee('6 months', false);
        $response->assertSee('1 year', false);
        $response->assertSee('20 years', false);
    }

    public function test_unrealistic_age_is_rejected_on_store(): void
    {
        $user = User::factory()->create();

        // 25 years – exceeds the 20-year cap
        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'OldDog',
            'species'     => 'Dog',
            'age'         => '25 years',
            'gender'      => 'Male',
            'location'    => 'Zone 1, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'A very old dog.',
        ]);

        $response->assertSessionHasErrors('age');
        $this->assertDatabaseMissing('pets', ['name' => 'Old Dog']);
    }

    public function test_unrealistic_age_is_rejected_on_update(): void
    {
        $user = User::factory()->create();
        $pet = Pet::create([
            'user_id'     => $user->id,
            'name'        => 'Bantay Edit',
            'species'     => 'Dog',
            'age'         => '3 years',
            'gender'      => 'Male',
            'location'    => 'Zone 2, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'A test dog.',
        ]);

        // Submit update with 30 years – should fail
        $response = $this->actingAs($user)->put(route('posts.update', $pet), [
            'name'        => 'Bantay Edit',
            'species'     => 'Dog',
            'age'         => '30 years',
            'gender'      => 'Male',
            'location'    => 'Zone 2, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'Updated.',
        ]);

        $response->assertSessionHasErrors('age');
        // Age should not have been changed
        $this->assertEquals('3 years', $pet->fresh()->getRawOriginal('age'));
    }

    public function test_valid_age_within_limit_is_accepted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'Veteran',
            'species'     => 'Dog',
            'age'         => '20 years',
            'gender'      => 'Male',
            'location'    => 'Zone 1, Brgy. 176-F',
            'status'      => 'Available',
            'description' => 'A 20-year-old dog.',
            'pet_images'  => [$this->fakeImage('veteran.png')],
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('pets', ['name' => 'Veteran']);
    }

    public function test_published_post_requires_at_least_one_image(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'Test Pet',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '1 year',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'description' => 'A cute dog without image.',
            // No pet_images provided
        ]);

        $response->assertSessionHasErrors('pet_images');
    }

    public function test_post_can_upload_multiple_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'Multi Photo Dog',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'description' => 'A dog with multiple photos.',
            'pet_images'  => [
                $this->fakeImage('dog1.png'),
                $this->fakeImage('dog2.png'),
                $this->fakeImage('dog3.png'),
            ],
        ]);

        $response->assertRedirect(route('home'));

        $pet = Pet::where('name', 'Multi Photo Dog')->first();
        $this->assertNotNull($pet);
        $this->assertNotNull($pet->image_path);

        // image_path should be JSON encoded array of 3 paths
        $this->assertCount(3, $pet->image_paths);
        $this->assertCount(3, $pet->image_urls);
    }

    public function test_post_image_count_cannot_exceed_5(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), [
            'name'        => 'Too Many Photos',
            'species'     => 'Dog',
            'breed'       => 'Aspin',
            'age'         => '2 years',
            'gender'      => 'Male',
            'location'    => 'Brgy. 176-F',
            'description' => 'Too many photos.',
            'pet_images'  => [
                $this->fakeImage('dog1.png'),
                $this->fakeImage('dog2.png'),
                $this->fakeImage('dog3.png'),
                $this->fakeImage('dog4.png'),
                $this->fakeImage('dog5.png'),
                $this->fakeImage('dog6.png'), // 6 images, exceeds limit of 5
            ],
        ]);

        $response->assertSessionHasErrors('pet_images');
    }

    public function test_pet_card_renders_verified_badge_for_verified_user(): void
    {
        $verifiedUser = User::factory()->create(['is_verified' => true]);

        $pet = Pet::create([
            'user_id' => $verifiedUser->id,
            'name' => 'Verified Pup',
            'species' => 'Dog',
            'age' => '1 year',
            'gender' => 'Male',
            'location' => 'Brgy. 176-F',
            'status' => 'Available',
            'description' => 'A sweet dog from a verified user.',
            'is_verified' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verified badge and check icon
        $response->assertSee('pet-verified-badge', false);
        $response->assertSee('Verified', false);
    }

    public function test_pet_card_renders_multi_photo_counter_badge(): void
    {
        $user = User::factory()->create();
        $petMulti = Pet::create([
            'user_id' => $user->id,
            'name' => 'Gallery Pup',
            'species' => 'Dog',
            'age' => '1 year',
            'gender' => 'Male',
            'location' => 'Brgy. 176-F',
            'status' => 'Available',
            'description' => 'Pup with multiple photos.',
            'image_path' => json_encode(['pets/img1.jpg', 'pets/img2.jpg', 'pets/img3.jpg', 'pets/img4.jpg', 'pets/img5.jpg']),
            'is_verified' => true,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Should render +4 badge on pet card
        $response->assertSee('pet-photos-badge', false);
        $response->assertSee('+4', false);
    }
}
