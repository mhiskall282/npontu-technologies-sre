<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\SecurityLoginNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows authenticated operator to update their profile and department', function () {
    $user = User::factory()->create([
        'name' => 'Original Operator',
        'department' => 'Core Operations (NOC)',
        'designation' => 'Junior Operator',
        'phone' => '+233 20 000 0000',
    ]);

    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/me', [
        'name' => 'Updated Operator Name',
        'department' => 'Infrastructure & Cloud',
        'designation' => 'Lead Cloud SRE',
        'phone' => '+233 24 999 8888',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Updated Operator Name',
                'department' => 'Infrastructure & Cloud',
                'designation' => 'Lead Cloud SRE',
                'phone' => '+233 24 999 8888',
            ],
        ]);

    $user->refresh();
    expect($user->name)->toBe('Updated Operator Name')
        ->and($user->department)->toBe('Infrastructure & Cloud')
        ->and($user->designation)->toBe('Lead Cloud SRE')
        ->and($user->phone)->toBe('+233 24 999 8888');
});

it('rejects invalid department value with 422 validation error', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->putJson('/api/v1/me', [
        'department' => 'Non Existent Department XYZ',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['department']);
});

it('rejects unauthenticated attempts to update profile with 401', function () {
    $response = $this->putJson('/api/v1/me', [
        'name' => 'Should Fail',
    ]);

    $response->assertStatus(401);
});

it('dispatches login security email and creates operational notification on mobile login', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'operator.mobile@npontu.com',
        'password' => bcrypt('SecurePassword123!'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'operator.mobile@npontu.com',
        'password' => 'SecurePassword123!',
        'device_name' => 'Pixel 8 Pro (Android 14)',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['token', 'token_type', 'user'],
        ]);

    Notification::assertSentTo($user, SecurityLoginNotification::class);

    $this->assertDatabaseHas('operational_notifications', [
        'user_id' => $user->id,
        'type' => 'system',
        'title' => 'New Mobile Session Authenticated',
    ]);
});
