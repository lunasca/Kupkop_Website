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
}
