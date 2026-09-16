<?php

declare(strict_types=1);

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('lists conversations and unread counts for authenticated operator', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $conv = Conversation::create([
        'type' => 'team',
        'title' => 'Core Infrastructure Alerts',
    ]);

    ConversationParticipant::create([
        'conversation_id' => $conv->id,
        'user_id' => $user->id,
        'last_read_at' => now()->subHour(),
    ]);

    Message::create([
        'conversation_id' => $conv->id,
        'sender_id' => $other->id,
        'body' => 'Notice: High CPU load on redis replica.',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/conversations');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonFragment([
            'id' => $conv->id,
            'title' => 'Core Infrastructure Alerts',
            'unread_count' => 1,
        ]);
});

it('posts a message with optional base64 image attachment', function () {
    $user = User::factory()->create();
    $conv = Conversation::create([
        'type' => 'team',
        'title' => 'Shift War Room',
        'is_private' => false,
    ]);

    ConversationParticipant::create([
        'conversation_id' => $conv->id,
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/conversations/{$conv->id}/messages", [
        'body' => 'Investigating gateway latency spike from grafana.',
        'attachment_name' => 'latency_chart.png',
        'attachment_mime' => 'image/png',
        'attachment_size' => 102400,
        'attachment_blob' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => [
                'body' => 'Investigating gateway latency spike from grafana.',
                'attachment_name' => 'latency_chart.png',
                'has_attachment' => true,
                'is_image' => true,
            ],
        ]);

    expect(Message::where('attachment_name', 'latency_chart.png')->exists())->toBeTrue();
});
