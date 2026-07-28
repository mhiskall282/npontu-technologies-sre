<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\User;

it('allows lead and admin users to create activities', function () {
    $lead = User::factory()->create(['role' => 'lead']);

    $response = $this->actingAs($lead)
        ->post('/activities', [
            'title' => 'Daily Service Status Check',
            'description' => 'Verify service is running.',
            'category' => 'Uptime',
            'recurrence' => 'daily',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activities', [
        'title' => 'Daily Service Status Check',
        'category' => 'Uptime',
        'created_by' => $lead->id,
    ]);
});

it('does not allow agent users to create activities', function () {
    $agent = User::factory()->create(['role' => 'agent']);

    $response = $this->actingAs($agent)
        ->post('/activities', [
            'title' => 'Should Not Save',
            'description' => 'Test',
            'category' => 'Uptime',
            'recurrence' => 'daily',
        ]);

    $response->assertForbidden();
});

it('allows authenticated users to view activities index and show pages', function () {
    $agent = User::factory()->create(['role' => 'agent']);
    $activity = Activity::factory()->create();

    $response = $this->actingAs($agent)
        ->get('/activities');

    $response->assertOk();

    $response = $this->actingAs($agent)
        ->get("/activities/{$activity->id}");

    $response->assertOk();
});

it('allows lead and admin users to update activity details', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activity = Activity::factory()->create();

    $response = $this->actingAs($admin)
        ->put("/activities/{$activity->id}", [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'category' => 'New Category',
            'recurrence' => 'daily',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activities', [
        'id' => $activity->id,
        'title' => 'Updated Title',
        'category' => 'New Category',
    ]);
});

it('allows admin users to delete activities (soft-delete)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activity = Activity::factory()->create();

    $response = $this->actingAs($admin)
        ->delete("/activities/{$activity->id}");

    $response->assertRedirect();
    $this->assertSoftDeleted('activities', [
        'id' => $activity->id,
    ]);
});
