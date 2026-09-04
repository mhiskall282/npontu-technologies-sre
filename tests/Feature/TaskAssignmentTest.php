<?php

declare(strict_types=1);

use App\Livewire\DailyActivityBoard;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\User;
use Livewire\Livewire;

it('allows lead and admin users to create an activity with an assigned engineer', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $engineer = User::factory()->create(['role' => 'agent', 'name' => 'Kofi Mensah']);

    $response = $this->actingAs($admin)
        ->post('/activities', [
            'title' => 'Daily SMS Gateway Reconciliation',
            'description' => 'Reconcile SMS traffic against gateway counters.',
            'category' => 'Application',
            'recurrence' => 'daily',
            'assigned_to' => $engineer->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('activities', [
        'title' => 'Daily SMS Gateway Reconciliation',
        'created_by' => $admin->id,
        'assigned_to' => $engineer->id,
    ]);

    $activity = Activity::where('title', 'Daily SMS Gateway Reconciliation')->first();
    expect($activity->assignee->id)->toBe($engineer->id);
});

it('allows updating an activity to assign or reassign team members and records audit diff', function () {
    $lead = User::factory()->create(['role' => 'lead']);
    $engineerA = User::factory()->create(['role' => 'agent']);
    $engineerB = User::factory()->create(['role' => 'agent']);

    $activity = Activity::factory()->create([
        'title' => 'Network Switch Health Check',
        'assigned_to' => $engineerA->id,
    ]);

    $response = $this->actingAs($lead)
        ->patch("/activities/{$activity->id}", [
            'title' => 'Network Switch Health Check',
            'category' => 'Network',
            'recurrence' => 'daily',
            'assigned_to' => $engineerB->id,
        ]);

    $response->assertRedirect();
    $activity->refresh();

    expect($activity->assigned_to)->toBe($engineerB->id);

    $auditLog = AuditLog::where('subject_type', Activity::class)
        ->where('subject_id', $activity->id)
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->old_values['assigned_to'] ?? null)->toBe($engineerA->id);
    expect($auditLog->new_values['assigned_to'] ?? null)->toBe($engineerB->id);
});

it('filters the daily activity board by assigned to me and unassigned shift pool', function () {
    $currentUser = User::factory()->create(['role' => 'agent', 'name' => 'Current Agent']);
    $otherUser = User::factory()->create(['role' => 'agent', 'name' => 'Other Agent']);

    $myActivity = Activity::factory()->create([
        'title' => 'Personal Assigned Task',
        'assigned_to' => $currentUser->id,
        'is_active' => true,
    ]);

    $otherActivity = Activity::factory()->create([
        'title' => 'Colleague Assigned Task',
        'assigned_to' => $otherUser->id,
        'is_active' => true,
    ]);

    $poolActivity = Activity::factory()->create([
        'title' => 'General Pool Task',
        'assigned_to' => null,
        'is_active' => true,
    ]);

    // Test filter "Assigned to Me"
    Livewire::actingAs($currentUser)
        ->test(DailyActivityBoard::class)
        ->set('assigneeFilter', 'me')
        ->assertSee('Personal Assigned Task')
        ->assertDontSee('Colleague Assigned Task')
        ->assertDontSee('General Pool Task');

    // Test filter "Unassigned"
    Livewire::actingAs($currentUser)
        ->test(DailyActivityBoard::class)
        ->set('assigneeFilter', 'unassigned')
        ->assertSee('General Pool Task')
        ->assertDontSee('Personal Assigned Task')
        ->assertDontSee('Colleague Assigned Task');
});

it('allows supervisors to assign tasks inline directly from the daily activity board', function () {
    $lead = User::factory()->create(['role' => 'lead']);
    $agent = User::factory()->create(['role' => 'agent']);
    $activity = Activity::factory()->create([
        'title' => 'Core DB Replication Health',
        'assigned_to' => null,
        'is_active' => true,
    ]);

    Livewire::actingAs($lead)
        ->test(DailyActivityBoard::class)
        ->call('assignActivity', $activity->id, $agent->id)
        ->assertSee("assigned to {$agent->name}");

    $activity->refresh();
    expect($activity->assigned_to)->toBe($agent->id);
});
