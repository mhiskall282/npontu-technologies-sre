<?php

declare(strict_types=1);

use App\Livewire\ActivityStatusUpdater;
use App\Livewire\DailyActivityBoard;
use App\Models\Activity;
use App\Models\ActivityLog;
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
        ->assertHasNoErrors()
        ->assertDispatched('status-updated')
        ->assertDispatched('statusUpdated');

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

it('refreshes the daily activity board immediately when status-updated event is dispatched', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $activity = Activity::factory()->create([
        'title' => 'API Health Check',
        'category' => 'Infrastructure',
    ]);
    $date = today()->format('Y-m-d');

    $board = Livewire::actingAs($user)
        ->test(DailyActivityBoard::class)
        ->assertSee('API Health Check')
        ->assertSeeHtml('Needs Attention');

    // Now log the activity as done
    ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => $date,
        'status' => 'done',
        'remark' => 'Checked and healthy.',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    // Dispatch event to board
    $board->dispatch('status-updated', message: "Status for '{$activity->title}' marked as Done.")
        ->assertSee("Status for '{$activity->title}' marked as Done.")
        ->assertSeeHtml('Completed')
        ->assertSee('Checked and healthy.');
});

it('filters activities on the daily board by search term and category', function () {
    $user = User::factory()->create(['role' => 'agent']);
    $smsActivity = Activity::factory()->create([
        'title' => 'Daily SMS count vs SMS count from logs',
        'category' => 'Application',
    ]);
    $dbActivity = Activity::factory()->create([
        'title' => 'Database backup verification',
        'category' => 'Database',
    ]);

    Livewire::actingAs($user)
        ->test(DailyActivityBoard::class)
        ->assertSee('Daily SMS count vs SMS count from logs')
        ->assertSee('Database backup verification')
        // Filter by search query
        ->set('search', 'backup')
        ->assertSee('Database backup verification')
        ->assertDontSee('Daily SMS count vs SMS count from logs')
        // Reset search and filter by category
        ->set('search', '')
        ->set('category', 'Application')
        ->assertSee('Daily SMS count vs SMS count from logs')
        ->assertDontSee('Database backup verification')
        // Clear filters
        ->call('clearFilters')
        ->assertSee('Daily SMS count vs SMS count from logs')
        ->assertSee('Database backup verification');
});
