<?php

declare(strict_types=1);

use App\Models\User;

it('redirects unauthenticated users to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

it('renders the login page', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertViewIs('auth.login');
});

it('authenticates a user with correct credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/');
});

it('does not authenticate a user with incorrect credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('email');
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
