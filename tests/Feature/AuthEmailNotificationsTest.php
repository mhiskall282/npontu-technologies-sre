<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\QueuedResetPasswordNotification;
use App\Notifications\SecurityLoginNotification;
use Illuminate\Support\Facades\Notification;

test('it sends branded SRE queued password reset notification on reset request', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'sre.operator@npontu.local']);

    $user->sendPasswordResetNotification('sample-reset-token-xyz');

    Notification::assertSentTo($user, QueuedResetPasswordNotification::class, function ($notification) {
        return $notification->token === 'sample-reset-token-xyz';
    });
});

test('it renders the branded SRE password reset email template with security notices', function () {
    $user = User::factory()->create([
        'name' => 'Abena Owusu',
        'email' => 'abena@npontu.local',
    ]);

    $notification = new QueuedResetPasswordNotification('test-token-123');
    $mailMessage = $notification->toMail($user);

    $html = view($mailMessage->view, $mailMessage->viewData)->render();

    expect($html)
        ->toContain('SRE Account Password Reset')
        ->toContain('Abena Owusu')
        ->toContain('60 minutes')
        ->toContain('Operational Security Safeguard')
        ->toContain('security@npontu.local');
});

test('it renders the branded SRE security login alert email with session details', function () {
    $user = User::factory()->create([
        'name' => 'Kwame Mensah',
        'email' => 'kwame@npontu.local',
        'grade' => 'L4',
        'role' => 'admin',
    ]);

    $notification = new SecurityLoginNotification('197.251.134.50', '2026-09-05 04:30:00');
    $mailMessage = $notification->toMail($user);

    $html = view($mailMessage->view, $mailMessage->viewData)->render();

    expect($html)
        ->toContain('New SRE Session Initiated')
        ->toContain('Kwame Mensah')
        ->toContain('197.251.134.50')
        ->toContain('Accra-Cluster-01')
        ->toContain('sre-emergency@npontu.local');
});
