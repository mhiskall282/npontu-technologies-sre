<?php

declare(strict_types=1);

use App\Models\OperationalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('rejects unauthenticated requests to list notifications with 401', function () {
    $response = $this->getJson('/api/v1/notifications');

    $response->assertStatus(401);
});

it('allows authenticated operators to list their operational notifications with unread count', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $otherUser = User::factory()->create(['role' => 'agent']);

    OperationalNotification::factory()->count(3)->create([
        'user_id' => $user->id,
        'read_at' => null,
    ]);

    OperationalNotification::factory()->create([
        'user_id' => $user->id,
        'read_at' => now(),
    ]);

    // Another user's notifications should not be returned
    OperationalNotification::factory()->count(2)->create([
        'user_id' => $otherUser->id,
        'read_at' => null,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/notifications');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonPath('meta.unread_count', 3)
        ->assertJsonPath('meta.total', 4)
        ->assertJsonCount(4, 'data');
});

it('filters notifications by unread_only and type', function () {
    $user = User::factory()->create(['role' => 'agent']);

    OperationalNotification::factory()->create([
        'user_id' => $user->id,
        'type' => 'escalation',
        'read_at' => null,
    ]);

    OperationalNotification::factory()->create([
        'user_id' => $user->id,
        'type' => 'handover',
        'read_at' => now(),
    ]);

    Sanctum::actingAs($user);

    $unreadResponse = $this->getJson('/api/v1/notifications?unread_only=true');
    $unreadResponse->assertStatus(200)
        ->assertJsonCount(1, 'data');

    $typeResponse = $this->getJson('/api/v1/notifications?type=escalation');
    $typeResponse->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'escalation');
});

it('allows authenticated operator to mark a single notification as read', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $notification = OperationalNotification::factory()->unread()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/v1/notifications/{$notification->id}/read");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'is_read' => true,
            ],
        ]);

    expect($notification->fresh()->isRead())->toBeTrue();
});

it('forbids an operator from marking another operators notification as read', function () {
    $user1 = User::factory()->create(['role' => 'agent']);
    $user2 = User::factory()->create(['role' => 'agent']);

    $notification = OperationalNotification::factory()->unread()->create([
        'user_id' => $user2->id,
    ]);

    Sanctum::actingAs($user1);

    $response = $this->postJson("/api/v1/notifications/{$notification->id}/read");

    $response->assertStatus(403);
    expect($notification->fresh()->isRead())->toBeFalse();
});

it('allows authenticated operator to mark all unread notifications as read', function () {
    $user = User::factory()->create(['role' => 'agent']);

    OperationalNotification::factory()->count(4)->unread()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/notifications/read-all');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'updated_count' => 4,
            ],
        ]);

    expect(OperationalNotification::where('user_id', $user->id)->whereNull('read_at')->count())->toBe(0);
});

it('allows authenticated operator to delete their own notification', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $notification = OperationalNotification::factory()->create([
        'user_id' => $user->id,
    ]);

    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");

    $response->assertStatus(204);
    expect(OperationalNotification::find($notification->id))->toBeNull();
});

it('forbids an operator from deleting another operators notification', function () {
    $user1 = User::factory()->create(['role' => 'agent']);
    $user2 = User::factory()->create(['role' => 'agent']);

    $notification = OperationalNotification::factory()->create([
        'user_id' => $user2->id,
    ]);

    Sanctum::actingAs($user1);

    $response = $this->deleteJson("/api/v1/notifications/{$notification->id}");

    $response->assertStatus(403);
    expect(OperationalNotification::find($notification->id))->not->toBeNull();
});
