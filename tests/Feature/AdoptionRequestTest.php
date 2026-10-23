<?php

namespace Tests\Feature;

use App\Models\AdoptionRequest;
use App\Models\Message;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdoptionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_adoption_request(): void
    {
        $response = $this->post('/adoption-requests', [
            'pet_id' => 1,
            'applicant_name' => 'Guest User',
            'contact_number' => '09123456789',
            'barangay' => 'Brgy. 176-F',
        ]);

        $response->assertRedirect();
    }

    public function test_user_can_submit_adoption_request_and_it_links_to_rescuer(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Playful puppy',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        // 1. Adopter submits adoption request
        $response = $this->actingAs($adopter)->post('/adoption-requests', [
            'pet_id' => $pet->id,
            'applicant_name' => $adopter->name,
            'contact_number' => '0917-555-1234',
            'email' => $adopter->email,
            'barangay' => 'Brgy. 176-F, Caloocan City',
            'message' => 'I have a safe backyard and lots of love to give yorkie!',
        ]);

        $response->assertRedirect(route('my-posts'));
        $response->assertSessionHas('success');

        // Verify adoption_requests table
        $this->assertDatabaseHas('adoption_requests', [
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_name' => $adopter->name,
            'contact_number' => '0917-555-1234',
            'status' => 'Pending',
        ]);

        // Verify pet status auto-set to Pending
        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'status' => 'Pending',
        ]);

        // Verify automated message created
        $this->assertDatabaseHas('messages', [
            'sender_id' => $adopter->id,
            'receiver_id' => $rescuer->id,
            'pet_id' => $pet->id,
            'subject' => 'Adoption Request: yorkie',
        ]);

        // 2. Rescuer sees the incoming request in My Posts
        $rescuerPage = $this->actingAs($rescuer)->get('/my-posts');
        $rescuerPage->assertStatus(200);
        $rescuerPage->assertSee('Incoming Adoption Requests (1)');
        $rescuerPage->assertSee('Lawrence Dullo');

        // 3. Adopter sees their application under My Submitted Adoption Applications
        $adopterPage = $this->actingAs($adopter)->get('/my-posts');
        $adopterPage->assertStatus(200);
        $adopterPage->assertSee('My Submitted Adoption Applications (1)');
        $adopterPage->assertSee('yorkie');

        // 4. Duplicate pending request is prevented
        $duplicateResponse = $this->actingAs($adopter)->post('/adoption-requests', [
            'pet_id' => $pet->id,
            'applicant_name' => $adopter->name,
            'contact_number' => '0917-555-1234',
            'barangay' => 'Brgy. 176-F',
        ]);
        $duplicateResponse->assertSessionHas('info');

        // 5. Rescuer can accept request
        $app = AdoptionRequest::where('pet_id', $pet->id)->first();
        $acceptResponse = $this->actingAs($rescuer)->patch("/requests/{$app->id}/status", [
            'status' => 'Accepted'
        ]);
        $acceptResponse->assertRedirect();
        $this->assertDatabaseHas('adoption_requests', [
            'id' => $app->id,
            'status' => 'Accepted'
        ]);

        // Verify pet status auto-set to Adopted
        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'status' => 'Adopted',
        ]);

        // Verify automated acceptance system message sent to applicant
        $this->assertDatabaseHas('messages', [
            'sender_id'   => $rescuer->id,
            'receiver_id' => $adopter->id,
            'pet_id'      => $pet->id,
            'subject'     => 'Adoption Request Accepted: yorkie',
            'message'     => 'Margarette Karylle De Luna has accepted your adoption request for yorkie.',
        ]);
    }

    public function test_user_cannot_adopt_own_pet(): void
    {
        $user = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $user->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Playful puppy',
            'status' => 'Available',
            'posted_by' => $user->name,
        ]);

        $response = $this->actingAs($user)->post('/adoption-requests', [
            'pet_id' => $pet->id,
            'applicant_name' => $user->name,
            'contact_number' => '0917-000-0000',
            'barangay' => 'Brgy. 176-F',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_user_can_cancel_adoption_request_deletes_record_notifies_rescuer_and_reverts_pet_status(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Playful puppy',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        // Submit adoption request
        $this->actingAs($adopter)->post('/adoption-requests', [
            'pet_id' => $pet->id,
            'applicant_name' => $adopter->name,
            'contact_number' => '0917-555-1234',
            'barangay' => 'Brgy. 176-F',
        ]);

        $app = AdoptionRequest::where('pet_id', $pet->id)->where('user_id', $adopter->id)->firstOrFail();
        $this->assertEquals('Pending', $pet->fresh()->status);

        // Cancel the adoption request
        $cancelResponse = $this->actingAs($adopter)->delete("/requests/{$app->id}/cancel");
        $cancelResponse->assertRedirect();
        $cancelResponse->assertSessionHas('success');

        // 1. Application record is completely deleted
        $this->assertDatabaseMissing('adoption_requests', [
            'id' => $app->id,
        ]);

        // 2. Pet status reverted back to Available
        $this->assertEquals('Available', $pet->fresh()->status);

        // 3. Automated notification message sent in chat thread
        $this->assertDatabaseHas('messages', [
            'sender_id' => $adopter->id,
            'receiver_id' => $rescuer->id,
            'pet_id' => $pet->id,
            'subject' => 'Adoption Request Cancelled: ' . $pet->name,
            'message' => 'Lawrence Dullo cancelled their adoption request for yorkie.',
        ]);
    }

    public function test_cancelling_request_does_not_revert_status_if_another_pending_request_exists(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter1 = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Playful puppy',
            'status' => 'Pending',
            'posted_by' => $rescuer->name,
        ]);

        $app1 = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter1->id,
            'applicant_name' => $adopter1->name,
            'contact_number' => '0917-111-1111',
            'barangay' => 'Brgy. 176-F',
            'status' => 'Pending',
        ]);

        $app2 = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter2->id,
            'applicant_name' => $adopter2->name,
            'contact_number' => '0917-222-2222',
            'barangay' => 'Brgy. 176-F',
            'status' => 'Pending',
        ]);

        // Adopter 1 cancels their request
        $this->actingAs($adopter1)->delete("/requests/{$app1->id}/cancel");

        // Record 1 is deleted
        $this->assertDatabaseMissing('adoption_requests', ['id' => $app1->id]);

        // Pet status remains Pending because app2 is still Pending
        $this->assertEquals('Pending', $pet->fresh()->status);
    }

    public function test_user_cannot_cancel_another_users_request(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter1 = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter2 = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'Playful puppy',
            'status' => 'Pending',
            'posted_by' => $rescuer->name,
        ]);

        $app1 = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter1->id,
            'applicant_name' => $adopter1->name,
            'contact_number' => '0917-111-1111',
            'barangay' => 'Brgy. 176-F',
            'status' => 'Pending',
        ]);

        // Adopter 2 attempts to cancel Adopter 1's request
        $response = $this->actingAs($adopter2)->delete("/requests/{$app1->id}/cancel");
        $response->assertStatus(403);

        // Record still exists
        $this->assertDatabaseHas('adoption_requests', ['id' => $app1->id]);
    }

    public function test_user_can_submit_adoption_request_with_address_field_and_static_barangay_context(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Muning',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '1 Year',
            'gender' => 'Female',
            'description' => 'Sweet cat',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        $response = $this->actingAs($adopter)->post('/adoption-requests', [
            'pet_id' => $pet->id,
            'applicant_name' => $adopter->name,
            'contact_number' => '0918-999-8888',
            'email' => $adopter->email,
            'address' => 'Package 6, Block 12, Lot 4',
        ]);

        $response->assertRedirect(route('my-posts'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('adoption_requests', [
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_name' => $adopter->name,
            'barangay' => 'Package 6, Block 12, Lot 4, Brgy. 176-F',
            'status' => 'Pending',
        ]);
    }

    public function test_incoming_requests_table_shows_only_pending_requests_and_hides_accepted_or_declined(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter1 = User::create([
            'name' => 'Applicant One',
            'email' => 'app1@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter2 = User::create([
            'name' => 'Applicant Two',
            'email' => 'app2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter3 = User::create([
            'name' => 'Applicant Three',
            'email' => 'app3@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Brownie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '6 Months',
            'gender' => 'Male',
            'description' => 'Gentle dog',
            'status' => 'Pending',
            'posted_by' => $rescuer->name,
        ]);

        // Pending request (should show)
        AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter1->id,
            'applicant_name' => 'Applicant One',
            'contact_number' => '0911-111-1111',
            'barangay' => 'Package 1, Brgy. 176-F',
            'status' => 'Pending',
        ]);

        // Accepted request (should be hidden)
        AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter2->id,
            'applicant_name' => 'Applicant Two',
            'contact_number' => '0922-222-2222',
            'barangay' => 'Package 2, Brgy. 176-F',
            'status' => 'Accepted',
        ]);

        // Declined request (should be hidden)
        AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter3->id,
            'applicant_name' => 'Applicant Three',
            'contact_number' => '0933-333-3333',
            'barangay' => 'Package 3, Brgy. 176-F',
            'status' => 'Declined',
        ]);

        $response = $this->actingAs($rescuer)->get('/my-posts');
        $response->assertStatus(200);

        // Count in title should reflect only pending (1)
        $response->assertSee('Incoming Adoption Requests (1)');
        $response->assertSee('Applicant One');
        $response->assertDontSee('Applicant Two');
        $response->assertDontSee('Applicant Three');
    }

    public function test_rescuer_can_decline_adoption_request_and_it_disappears_from_incoming(): void
    {
        $rescuer = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'margarette@example.com',
            'password' => bcrypt('password123'),
        ]);

        $adopter = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Fluffy',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '1 Year',
            'gender' => 'Female',
            'description' => 'Cute cat',
            'status' => 'Pending',
            'posted_by' => $rescuer->name,
        ]);

        $req = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_name' => 'Lawrence Dullo',
            'contact_number' => '0917-555-1234',
            'barangay' => 'Package 4, Brgy. 176-F',
            'status' => 'Pending',
        ]);

        // Rescuer declines the request
        $response = $this->actingAs($rescuer)->patch("/requests/{$req->id}/status", [
            'status' => 'Declined',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Declined', $req->fresh()->status);
        $this->assertEquals('Available', $pet->fresh()->status);

        // Verify automated decline system message sent to applicant
        $this->assertDatabaseHas('messages', [
            'sender_id'   => $rescuer->id,
            'receiver_id' => $adopter->id,
            'pet_id'      => $pet->id,
            'subject'     => 'Adoption Request Declined: Fluffy',
            'message'     => 'Margarette Karylle De Luna has declined your adoption request for Fluffy.',
        ]);

        // Verify it no longer appears in incoming requests
        $pageResponse = $this->actingAs($rescuer)->get('/my-posts');
        $pageResponse->assertSee('Incoming Adoption Requests (0)');
        $pageResponse->assertSee('No incoming adoption requests at the moment');
    }

    public function test_user_can_delete_declined_or_cancelled_application(): void
    {
        $rescuer = User::create([
            'name' => 'Rescuer User',
            'email' => 'rescuer@example.com',
            'password' => bcrypt('password123'),
        ]);

        $applicant = User::create([
            'name' => 'Applicant User',
            'email' => 'applicant@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Milo',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '5 Months',
            'gender' => 'Male',
            'description' => 'Friendly puppy',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        $declinedApp = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $applicant->id,
            'applicant_name' => $applicant->name,
            'contact_number' => '0917-123-4567',
            'barangay' => 'Package 1, Brgy. 176-F',
            'status' => 'Declined',
        ]);

        $cancelledApp = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $applicant->id,
            'applicant_name' => $applicant->name,
            'contact_number' => '0917-123-4567',
            'barangay' => 'Package 1, Brgy. 176-F',
            'status' => 'Cancelled',
        ]);

        // Applicant deletes the declined application
        $deleteDeclinedResponse = $this->actingAs($applicant)->delete("/requests/{$declinedApp->id}");
        $deleteDeclinedResponse->assertRedirect();
        $this->assertDatabaseMissing('adoption_requests', ['id' => $declinedApp->id]);

        // Applicant deletes the cancelled application
        $deleteCancelledResponse = $this->actingAs($applicant)->delete("/requests/{$cancelledApp->id}");
        $deleteCancelledResponse->assertRedirect();
        $this->assertDatabaseMissing('adoption_requests', ['id' => $cancelledApp->id]);
    }

    public function test_user_cannot_delete_pending_or_accepted_application_via_destroy(): void
    {
        $rescuer = User::create([
            'name' => 'Rescuer User',
            'email' => 'rescuer2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $applicant = User::create([
            'name' => 'Applicant User',
            'email' => 'applicant2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Luna',
            'species' => 'Cat',
            'breed' => 'Puspin',
            'age' => '2 Months',
            'gender' => 'Female',
            'description' => 'Sweet kitten',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        $pendingApp = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $applicant->id,
            'applicant_name' => $applicant->name,
            'contact_number' => '0917-123-4567',
            'barangay' => 'Package 2, Brgy. 176-F',
            'status' => 'Pending',
        ]);

        $acceptedApp = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $applicant->id,
            'applicant_name' => $applicant->name,
            'contact_number' => '0917-123-4567',
            'barangay' => 'Package 2, Brgy. 176-F',
            'status' => 'Accepted',
        ]);

        // Attempt to delete pending application via destroy endpoint
        $pendingResponse = $this->actingAs($applicant)->delete("/requests/{$pendingApp->id}");
        $pendingResponse->assertSessionHas('error');
        $this->assertDatabaseHas('adoption_requests', ['id' => $pendingApp->id]);

        // Attempt to delete accepted application via destroy endpoint
        $acceptedResponse = $this->actingAs($applicant)->delete("/requests/{$acceptedApp->id}");
        $acceptedResponse->assertSessionHas('error');
        $this->assertDatabaseHas('adoption_requests', ['id' => $acceptedApp->id]);
    }

    public function test_my_applications_ui_shows_delete_button_only_for_declined_or_cancelled(): void
    {
        $rescuer = User::create([
            'name' => 'Rescuer User',
            'email' => 'rescuer3@example.com',
            'password' => bcrypt('password123'),
        ]);

        $applicant = User::create([
            'name' => 'Applicant User',
            'email' => 'applicant3@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $rescuer->id,
            'name' => 'Buddy',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '1 Year',
            'gender' => 'Male',
            'description' => 'Good boy',
            'status' => 'Available',
            'posted_by' => $rescuer->name,
        ]);

        $declinedApp = AdoptionRequest::create([
            'pet_id' => $pet->id,
            'user_id' => $applicant->id,
            'applicant_name' => $applicant->name,
            'contact_number' => '0917-123-4567',
            'barangay' => 'Package 3, Brgy. 176-F',
            'status' => 'Declined',
        ]);

        $response = $this->actingAs($applicant)->get('/my-posts');
        $response->assertStatus(200);
        $response->assertSee('confirmDeleteApplication(' . $declinedApp->id);
    }
}
