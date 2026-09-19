<?php

declare(strict_types=1);

use App\Mail\OrganizationWelcomeMail;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Mail;

it('renders the registration screen', function () {
    $response = $this->get('/register');

    $response->assertOk()
        ->assertViewIs('auth.register')
        ->assertSee('Create SRE Account');
});

it('allows a new user to register with a new organization and sandbox workspace', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Sara Connor',
        'email' => 'sara@cyberdyne.io',
        'password' => 'CyberDyneOps2026!',
        'password_confirmation' => 'CyberDyneOps2026!',
        'organization_name' => 'Cyberdyne Systems',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'sara@cyberdyne.io')->first();
    expect($user)->not->toBeNull();
    $this->assertAuthenticatedAs($user);

    $org = Organization::where('name', 'Cyberdyne Systems')->first();
    expect($org)->not->toBeNull();
    expect($org->status)->toBe('active');
    expect($user->organizations->contains($org))->toBeTrue();

    // Verify primary workspace was provisioned
    $workspace = Workspace::where('organization_id', $org->id)->first();
    expect($workspace)->not->toBeNull();

    // Verify welcome email was sent
    Mail::assertQueued(OrganizationWelcomeMail::class, function ($mail) use ($user) {
        return $mail->user->id === $user->id;
    });
});

it('allows a user to join an existing organization via company code', function () {
    Mail::fake();

    $org = Organization::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tech Corp',
        'slug' => 'tech-corp',
        'company_code' => 'NPT-TCH-01',
        'status' => 'active',
        'tier' => 'team',
    ]);

    $ws = Workspace::create([
        'uuid' => (string) Str::uuid(),
        'organization_id' => $org->id,
        'name' => 'Tech Corp Main',
        'slug' => 'tech-corp-main',
        'is_personal' => false,
        'status' => 'active',
    ]);

    $response = $this->post('/register', [
        'name' => 'Kyle Reese',
        'email' => 'kyle@techcorp.com',
        'password' => 'TechCorpPass2026!',
        'password_confirmation' => 'TechCorpPass2026!',
        'company_code' => 'NPT-TCH-01',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'kyle@techcorp.com')->first();
    expect($user)->not->toBeNull();
    expect($org->users->contains($user))->toBeTrue();
    expect($ws->members->contains($user))->toBeTrue();
});

it('fails registration when an invalid company code is provided', function () {
    $response = $this->post('/register', [
        'name' => 'Invalid Code User',
        'email' => 'invalid@user.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'company_code' => 'DOES-NOT-EXIST',
    ]);

    $response->assertSessionHasErrors('company_code');
    $this->assertGuest();
});
