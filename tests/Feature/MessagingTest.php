<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected(): void
    {
        $response = $this->get('/messages');
        // Guest is redirected by auth middleware
        $response->assertRedirect();
    }

    public function test_user_can_view_threads_and_exchange_messages(): void
    {
        $user1 = User::create([
            'name' => 'Margarette Karylle De Luna',
            'email' => 'deluna@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Lawrence Dullo',
            'email' => 'lawrence@example.com',
            'password' => bcrypt('password123'),
        ]);

        $pet = Pet::create([
            'user_id' => $user1->id,
            'name' => 'yorkie',
            'species' => 'Dog',
            'breed' => 'Aspin',
            'age' => '3 Months',
            'gender' => 'Male',
            'description' => 'A lovely pup',
            'status' => 'Available',
            'posted_by' => $user1->name,
        ]);

        // Lawrence sends initial message to Margarette
        $initialMsg = Message::create([
            'sender_id' => $user2->id,
            'receiver_id' => $user1->id,
            'pet_id' => $pet->id,
            'subject' => 'Inquiry: yorkie',
            'message' => 'Hi Margarette, I would like to adopt yorkie!',
            'is_read' => false,
        ]);

        // Margarette logs in and visits /messages
        $response = $this->actingAs($user1)->get('/messages');
        $response->assertStatus(200);
        $response->assertSee('Lawrence Dullo');
        $response->assertSee('Hi Margarette, I would like to adopt yorkie!');

        // Margarette sends a reply to Lawrence
        $sendResponse = $this->actingAs($user1)->postJson('/messages', [
            'receiver_id' => $user2->id,
            'message' => 'Hello Lawrence! Yes, yorkie is available for adoption.',
            'subject' => 'Inquiry: yorkie',
            'pet_id' => $pet->id,
        ]);

        $sendResponse->assertStatus(200);
        $sendResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Hello Lawrence! Yes, yorkie is available for adoption.',
        ]);

        // Margarette marks messages from Lawrence as read
        $readResponse = $this->actingAs($user1)->postJson("/messages/{$user2->id}/read");
        $readResponse->assertStatus(200);
        $readResponse->assertJson(['success' => true, 'unread_total' => 0]);

        $this->assertDatabaseHas('messages', [
            'id' => $initialMsg->id,
            'is_read' => true,
        ]);

        // Lawrence polls messages from Margarette
        $pollResponse = $this->actingAs($user2)->getJson("/messages/{$user1->id}/poll");
        $pollResponse->assertStatus(200);
        $pollResponse->assertJsonFragment([
            'mine' => false,
            'text' => 'Hello Lawrence! Yes, yorkie is available for adoption.'
        ]);

        // 1. Message ordering: Chronological Ascending (oldest at top [0], newest at bottom [1])
        $pollMessages = $pollResponse->json('messages');
        $this->assertCount(2, $pollMessages);
        $this->assertEquals('Hi Margarette, I would like to adopt yorkie!', $pollMessages[0]['text']);
        $this->assertEquals('Hello Lawrence! Yes, yorkie is available for adoption.', $pollMessages[1]['text']);

        // 2. Date & Time formatting: Human-readable timestamp (e.g., 'Today at ...')
        $this->assertStringContainsString('Today at', $pollMessages[0]['time']);

        // 3. Status indicators (Sent vs Read)
        // Message 0 (Lawrence's original message) was marked read by Margarette
        $this->assertTrue($pollMessages[0]['is_read']);
        // Message 1 (Margarette's latest reply) has not been read by Lawrence yet
        $this->assertFalse($pollMessages[1]['is_read']);
    }

    public function test_message_timestamp_human_readable_formatting(): void
    {
        // Today
        $today = now()->setTime(10, 30);
        $this->assertEquals('Today at 10:30 AM', Message::formatHumanTimestamp($today));

        // Yesterday
        $yesterday = now()->subDay()->setTime(16, 15);
        $this->assertEquals('Yesterday at 4:15 PM', Message::formatHumanTimestamp($yesterday));

        // Older date in current year (e.g., Oct 22)
        $older = \Carbon\Carbon::create(now('Asia/Manila')->year, 10, 22, 14, 50, 0, 'Asia/Manila');
        if ($older->isToday() || $older->isYesterday()) {
            $older = $older->subDays(5);
        }
        $formatted = Message::formatHumanTimestamp($older);
        $this->assertStringContainsString(':', $formatted);

        // Verify UTC timestamp converts correctly to Philippine Standard Time (UTC+8)
        $utcTime = \Carbon\Carbon::create(2025, 1, 15, 4, 30, 0, 'UTC');
        $pstFormatted = Message::formatHumanTimestamp($utcTime);
        // 04:30 UTC is 12:30 PM PST
        $this->assertStringContainsString('12:30 PM', $pstFormatted);
        $this->assertStringContainsString('Jan 15', $pstFormatted);
    }

    public function test_sender_can_edit_own_message(): void
    {
        $user1 = User::create([
            'name' => 'Alice Sender',
            'email' => 'alice@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Bob Receiver',
            'email' => 'bob@example.com',
            'password' => bcrypt('password123'),
        ]);

        $message = Message::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Original sent message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user1)->patchJson("/messages/{$message->id}/edit", [
            'message' => 'Edited updated message',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'text' => 'Edited updated message',
        ]);
        $this->assertNotNull($response->json('edited_at'));

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'message' => 'Edited updated message',
            'original_message' => 'Original sent message',
        ]);
    }

    public function test_user_cannot_edit_others_message(): void
    {
        $user1 = User::create([
            'name' => 'Alice Sender',
            'email' => 'alice2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Bob Receiver',
            'email' => 'bob2@example.com',
            'password' => bcrypt('password123'),
        ]);

        $message = Message::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Alice message',
            'is_read' => false,
        ]);

        // Bob tries to edit Alice's message
        $response = $this->actingAs($user2)->patchJson("/messages/{$message->id}/edit", [
            'message' => 'Hacked message',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_message_for_everyone(): void
    {
        $user1 = User::create([
            'name' => 'Alice Del',
            'email' => 'alicedel@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Bob Del',
            'email' => 'bobdel@example.com',
            'password' => bcrypt('password123'),
        ]);

        $message = Message::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Sensitive secret message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user1)->deleteJson("/messages/{$message->id}", [
            'mode' => 'for_everyone',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'mode' => 'for_everyone',
            'deleted_for_everyone' => true,
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'deleted_for_everyone' => true,
        ]);

        // When polled by Bob, it shows deleted_for_everyone true
        $poll = $this->actingAs($user2)->getJson("/messages/{$user1->id}/poll");
        $poll->assertStatus(200);
        $poll->assertJsonFragment([
            'id' => $message->id,
            'deleted_for_everyone' => true,
        ]);
    }

    public function test_user_can_delete_message_for_me(): void
    {
        $user1 = User::create([
            'name' => 'Alice ForMe',
            'email' => 'aliceforme@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Bob ForMe',
            'email' => 'bobforme@example.com',
            'password' => bcrypt('password123'),
        ]);

        $message = Message::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Message to delete for me only',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user1)->deleteJson("/messages/{$message->id}", [
            'mode' => 'for_me',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'mode' => 'for_me',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'deleted_by_sender' => true,
        ]);

        // Alice's poll should NOT include this message
        $alicePoll = $this->actingAs($user1)->getJson("/messages/{$user2->id}/poll");
        $this->assertCount(0, $alicePoll->json('messages'));

        // Bob's poll SHOULD still include this message
        $bobPoll = $this->actingAs($user2)->getJson("/messages/{$user1->id}/poll");
        $this->assertCount(1, $bobPoll->json('messages'));
        $this->assertEquals('Message to delete for me only', $bobPoll->json('messages')[0]['text']);
    }

    public function test_user_can_delete_conversation(): void
    {
        $user1 = User::create([
            'name' => 'Alice Conv',
            'email' => 'aliceconv@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::create([
            'name' => 'Bob Conv',
            'email' => 'bobconv@example.com',
            'password' => bcrypt('password123'),
        ]);

        Message::create([
            'sender_id' => $user1->id,
            'receiver_id' => $user2->id,
            'message' => 'Hello in conv',
            'is_read' => false,
        ]);

        // Alice deletes conversation with Bob
        $response = $this->actingAs($user1)->deleteJson("/messages/{$user2->id}/conversation");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'partner_id' => $user2->id,
        ]);

        // When Alice visits /messages, conversation with Bob is hidden
        $aliceView = $this->actingAs($user1)->get('/messages');
        $aliceView->assertStatus(200);
        $aliceView->assertDontSee('Bob Conv');

        // When Bob visits /messages, conversation with Alice is still visible
        $bobView = $this->actingAs($user2)->get('/messages');
        $bobView->assertStatus(200);
        $bobView->assertSee('Alice Conv');
    }

    public function test_messages_ui_renders_edit_delete_and_conversation_modals(): void
    {
        $user = User::create([
            'name' => 'Alice UI',
            'email' => 'aliceui@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get('/messages');
        $response->assertStatus(200);

        // Asserts modal elements and handlers are present in blade
        $response->assertSee('deleteMessageModal');
        $response->assertSee('deleteConversationModal');
        $response->assertSee('convDropdownMenu');
        $response->assertSee('startEditMessage');
        $response->assertSee('openDeleteConversationModal');
        $response->assertSee('Delete for Everyone');
        $response->assertSee('Delete for Me');
        $response->assertSee('(edited)');
    }

    public function test_unread_count_endpoint_returns_correct_count(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create one unread message for user1 from user2
        Message::create([
            'sender_id'   => $user2->id,
            'receiver_id' => $user1->id,
            'message'     => 'Hey there!',
            'is_read'     => false,
            'subject'     => 'Test subject',
        ]);

        // Create one already-read message — should NOT be counted
        Message::create([
            'sender_id'   => $user2->id,
            'receiver_id' => $user1->id,
            'message'     => 'Already seen.',
            'is_read'     => true,
            'subject'     => 'Test subject',
        ]);

        $response = $this->actingAs($user1)->getJson('/messages/unread');

        $response->assertStatus(200)
                 ->assertJsonPath('unread_total', 1);
    }

    public function test_unread_count_endpoint_returns_zero_when_no_unread(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/messages/unread');

        $response->assertStatus(200)
                 ->assertJsonPath('unread_total', 0);
    }

    public function test_unread_count_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/messages/unread');
        // Unauthenticated requests must be redirected (auth middleware)
        $response->assertStatus(401);
    }
}
