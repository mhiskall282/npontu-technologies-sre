<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('authenticates valid credentials and returns sanctum bearer token', function () {
    $user = User::factory()->create([
        'email' => 'operator@npontu.com',
        'password' => Hash::make('CorrectPassword123!'),
        'role' => 'agent',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'operator@npontu.com',
        'password' => 'CorrectPassword123!',
        'device_name' => 'Pixel 9 Pro Test',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Authentication successful.',
            'data' => [
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'email' => 'operator@npontu.com',
                    'role' => 'agent',
                ],
            ],
        ]);

    expect($response->json('data.token'))->not->toBeEmpty();
});

it('rejects invalid password with 401 unauthenticated', function () {
    User::factory()->create([
        'email' => 'lead@npontu.com',
        'password' => Hash::make('ValidSecretPassword'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'lead@npontu.com',
        'password' => 'WrongPassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid email or password provided.',
        ]);
});

it('returns authenticated user profile on /api/v1/me', function () {
    $user = User::factory()->create([
        'name' => 'John Okyere',
        'email' => 'john@npontu.com',
        'role' => 'admin',
        'grade' => 'L5',
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/me');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => 'John Okyere',
                'email' => 'john@npontu.com',
                'role' => 'admin',
                'grade' => 'L5',
            ],
        ]);
});

it('revokes access token on logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Successfully logged out. Session revoked.',
        ]);
});
