<?php

declare(strict_types=1);

use App\Livewire\ActivityStatusUpdater;
use App\Models\Activity;
use App\Models\User;
use Livewire\Livewire;

it('updates status and remark via livewire component and writes audit and domain logs', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $activity = Activity::factory()->create();
    $date = today()->format('Y-m-d');

    Livewire::actingAs($user)
        ->test(ActivityStatusUpdater::class, [
            'activity' => $activity,
            'date' => $date,
            'currentStatus' => 'pending',
        ])
        ->set('status', 'done')
        ->set('remark', 'All SMS delivered successfully.')
        ->call('save')
        ->assertHasNoErrors();

    // Verify activity log is created (domain change)
    $this->assertDatabaseHas('activity_logs', [
        'activity_id' => $activity->id,
        'date' => $date,
        'status' => 'done',
        'remark' => 'All SMS delivered successfully.',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    // Verify audit log is created (security trail)
    $this->assertDatabaseHas('audit_logs', [
        'actor_id' => $user->id,
        'actor_name' => $user->name,
        'subject_type' => Activity::class,
        'subject_id' => $activity->id,
        'event' => 'status_changed',
    ]);
});
