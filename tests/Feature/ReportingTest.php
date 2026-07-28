<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\User;

it('queries activities within a custom date range and filters by status', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    // Create logs for different dates and statuses
    $logTodayDone = ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => today()->format('Y-m-d'),
        'status' => 'done',
        'remark' => 'Done today',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    $logTodayPending = ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => today()->format('Y-m-d'),
        'status' => 'pending',
        'remark' => 'Pending today',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    $logYesterdayDone = ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => today()->subDay()->format('Y-m-d'),
        'status' => 'done',
        'remark' => 'Done yesterday',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    $logThreeDaysAgo = ActivityLog::create([
        'activity_id' => $activity->id,
        'date' => today()->subDays(3)->format('Y-m-d'),
        'status' => 'done',
        'remark' => 'Out of range',
        'updated_by' => $user->id,
        'actor_name' => $user->name,
    ]);

    // Query date range: today to yesterday
    $response = $this->actingAs($user)
        ->get(route('reports.index', [
            'from' => today()->subDay()->format('Y-m-d'),
            'to' => today()->format('Y-m-d'),
        ]));

    $response->assertOk();
    $response->assertSee('Done today');
    $response->assertSee('Pending today');
    $response->assertSee('Done yesterday');
    $response->assertDontSee('Out of range');

    // Query date range with done status filter
    $responseFilterDone = $this->actingAs($user)
        ->get(route('reports.index', [
            'from' => today()->subDay()->format('Y-m-d'),
            'to' => today()->format('Y-m-d'),
            'status' => 'done',
        ]));

    $responseFilterDone->assertOk();
    $responseFilterDone->assertSee('Done today');
    $responseFilterDone->assertSee('Done yesterday');
    $responseFilterDone->assertDontSee('Pending today');
});
