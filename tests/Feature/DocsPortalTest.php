<?php

declare(strict_types=1);

test('it renders the comprehensive high-level SRE docs portal with 200 ok', function () {
    $response = $this->get(route('docs'));

    $response->assertStatus(200);
    $response->assertSee('Engineered for zero broken handovers. Documented for everyone.');
    $response->assertSee('SRE COMPREHENSIVE ARCHITECTURE');
    $response->assertSee('Pre-Seeded Operational Test Personas');
    $response->assertSee('Kwame Mensah');
    $response->assertSee('Abena Owusu');
    $response->assertSee('Kofi Asante');
});

test('docs portal presents technical architecture and verification commands', function () {
    $response = $this->get(route('docs'));

    $response->assertStatus(200);
    $response->assertSee('Laravel 11 LTS');
    $response->assertSee('Livewire 3 Real-Time Reactive Engine');
    $response->assertSee('MySQL 8.0+ InnoDB');
    $response->assertSee('Pest 3 Test Suite');
    $response->assertSee('php artisan test');
    $response->assertSee('php artisan reports:send-automated');
});

test('docs portal documents the two-way handover protocol and operational war rooms', function () {
    $response = $this->get(route('docs'));

    $response->assertStatus(200);
    $response->assertSee('The 4-Phase Two-Way Handover Protocol');
    $response->assertSee('Live Verification');
    $response->assertSee('Outgoing Sign-Off');
    $response->assertSee('Incoming Sign-On');
    $response->assertSee('Forensic Seal');
    $response->assertSee('@Mention Email Receipts');
});

test('docs portal includes the comprehensive interactive FAQ accordion', function () {
    $response = $this->get(route('docs'));

    $response->assertStatus(200);
    $response->assertSee('Frequently Asked Questions');
    $response->assertSee('How does the two-way handover custody transfer work mathematically?');
    $response->assertSee('120 minutes of inactivity');
    $response->assertSee('Can audit log records ever be deleted or edited by administrators?');
    $response->assertSee('How do automated daily, weekly, and monthly reports work?');
});

test('docs portal is linked from the public landing page navigation and footer', function () {
    $response = $this->get(route('landing'));

    $response->assertStatus(200);
    $response->assertSee(route('docs'));
    $response->assertSee('Docs &amp; Guide', false);
});
