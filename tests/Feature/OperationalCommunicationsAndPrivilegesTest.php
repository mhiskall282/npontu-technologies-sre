<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Handovers\CreateShiftHandoverAction;
use App\Livewire\DailyActivityBoard;
use App\Livewire\OperationalChat;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('incoming shift lead can formally accept and sign on to an operational handover', function () {
    $outgoing = User::factory()->create([
        'name' => 'Outgoing Lead Kwame',
        'role' => 'lead',
        'grade' => 'L4',
    ]);
    $incoming = User::factory()->create([
        'name' => 'Incoming Lead Abena',
        'role' => 'lead',
        'grade' => 'L4',
    ]);

    // Create handover
    $createAction = app(CreateShiftHandoverAction::class);
    $this->actingAs($outgoing);
    $handover = $createAction->execute([
        'date' => today()->format('Y-m-d'),
        'shift' => 'morning',
        'incoming_lead_id' => $incoming->id,
        'summary' => 'All payment gateways online. Backup jobs completed with 0 errors.',
        'incidents' => 'Minor latency spike on MTN MoMo at 10:15 GMT.',
        'pending_tasks_count' => 1,
        'completed_tasks_count' => 12,
    ]);

    expect($handover->isAccepted())->toBeFalse();

    // Incoming lead accepts handover via DailyActivityBoard Livewire component
    Livewire::actingAs($incoming)
        ->test(DailyActivityBoard::class)
        ->call('openAcceptModal', $handover->id)
        ->assertSet('showAcceptModal', true)
        ->assertSet('acceptingHandoverId', $handover->id)
        ->set('acceptanceRemarks', 'Systems and queue verified on morning transfer. Shift assumed.')
        ->set('confirmResponsibility', true)
        ->call('confirmAcceptHandover')
        ->assertSet('showAcceptModal', false)
        ->assertHasNoErrors();

    $handover->refresh();

    expect($handover->isAccepted())->toBeTrue()
        ->and($handover->accepted_by_id)->toBe($incoming->id)
        ->and($handover->acceptance_remarks)->toBe('Systems and queue verified on morning transfer. Shift assumed.')
        ->and($handover->accepted_at)->not->toBeNull();

    // Verify compliance audit trail
    $this->assertDatabaseHas('audit_logs', [
        'subject_type' => ShiftHandover::class,
        'subject_id' => $handover->id,
        'event' => 'handover_accepted',
    ]);
});

test('operator without accept_handovers privilege cannot accept handover', function () {
    $operator = User::factory()->create([
        'role' => 'agent',
        'privileges' => ['escalate_incidents'], // no accept_handovers
    ]);
    $handover = ShiftHandover::factory()->create();

    Livewire::actingAs($operator)
        ->test(DailyActivityBoard::class)
        ->call('openAcceptModal', $handover->id)
        ->set('confirmResponsibility', true)
        ->call('confirmAcceptHandover')
        ->assertForbidden();
});

test('admin can create user with custom grade, department, and granular privileges', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Ewuresi Mensah',
        'email' => 'ewuresi@npontu.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'agent',
        'grade' => 'L3',
        'department' => 'Cloud Infrastructure & SRE',
        'designation' => 'SRE Specialist',
        'phone' => '+233201234567',
        'privileges' => [
            'manage_activities',
            'assign_tasks',
            'create_channels',
        ],
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'ewuresi@npontu.com')->firstOrFail();

    expect($user->grade)->toBe('L3')
        ->and($user->department)->toBe('Cloud Infrastructure & SRE')
        ->and($user->hasPrivilege('manage_activities'))->toBeTrue()
        ->and($user->hasPrivilege('assign_tasks'))->toBeTrue()
        ->and($user->hasPrivilege('create_channels'))->toBeTrue()
        ->and($user->hasPrivilege('manage_users'))->toBeFalse(); // Not assigned
});

test('admin can update user privileges and security audit logs capture diff', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create([
        'role' => 'agent',
        'grade' => 'L1',
        'privileges' => ['escalate_incidents'],
    ]);

    $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'role' => 'agent',
        'grade' => 'L2',
        'department' => 'Database Operations & DBA',
        'designation' => 'Junior DBA',
        'phone' => $user->phone,
        'privileges' => [
            'escalate_incidents',
            'export_reports',
            'create_channels',
        ],
    ]);

    $response->assertRedirect(route('admin.users.index'));

    $user->refresh();
    expect($user->grade)->toBe('L2')
        ->and($user->hasPrivilege('export_reports'))->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'subject_type' => User::class,
        'subject_id' => $user->id,
        'event' => 'updated',
    ]);
});

test('operational chat initializes default team channel and allows sending messages', function () {
    $operator = User::factory()->create([
        'name' => 'Kojo Antwi',
        'role' => 'agent',
        'grade' => 'L2',
    ]);

    $component = Livewire::actingAs($operator)
        ->test(OperationalChat::class)
        ->assertSee('General Shift Operations')
        ->set('messageText', 'Night shift checks started. SMS gateway latency is nominal at 45ms.')
        ->call('sendMessage')
        ->assertSet('messageText', '')
        ->assertHasNoErrors();

    $generalChannel = Conversation::where('type', 'team')->where('title', 'General Shift Operations')->firstOrFail();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $generalChannel->id,
        'sender_id' => $operator->id,
        'body' => 'Night shift checks started. SMS gateway latency is nominal at 45ms.',
    ]);
});

test('users can start 1-on-1 direct chat and track unread message counts', function () {
    $userA = User::factory()->create(['name' => 'Alice SRE', 'role' => 'agent']);
    $userB = User::factory()->create(['name' => 'Bob DevOps', 'role' => 'lead']);

    // Alice starts direct chat with Bob
    Livewire::actingAs($userA)
        ->test(OperationalChat::class)
        ->call('startDirectChat', $userB->id)
        ->assertHasNoErrors();

    $directConv = Conversation::where('type', 'direct')->firstOrFail();
    expect($directConv->participants->pluck('id')->all())->toContain($userA->id, $userB->id);

    // Alice sends a message to Bob
    Livewire::actingAs($userA)
        ->test(OperationalChat::class, ['c' => $directConv->id])
        ->set('messageText', 'Hey Bob, can you review the PaySwitch latency threshold?')
        ->call('sendMessage');

    // Bob has 1 unread message
    expect($userB->unreadMessagesCount())->toBe(1);

    // Bob views the conversation
    Livewire::actingAs($userB)
        ->test(OperationalChat::class, ['c' => $directConv->id])
        ->assertSee('Hey Bob, can you review the PaySwitch latency threshold?');

    // Unread count drops to 0
    expect($userB->unreadMessagesCount())->toBe(0);
});

test('users with create_channels privilege can create group ops channels', function () {
    $lead = User::factory()->create([
        'role' => 'lead',
        'privileges' => ['create_channels'],
    ]);
    $engineer = User::factory()->create(['role' => 'agent']);

    Livewire::actingAs($lead)
        ->test(OperationalChat::class)
        ->call('openNewChatModal', 'group')
        ->set('channelTitle', 'MTN MoMo Gateway War Room')
        ->set('channelDescription', 'Dedicated incident triage channel for mobile money timeouts')
        ->set('isPrivateChannel', true)
        ->set('selectedParticipants', [$engineer->id])
        ->call('createGroupChannel')
        ->assertHasNoErrors();

    $channel = Conversation::where('title', 'MTN MoMo Gateway War Room')->firstOrFail();
    expect($channel->is_private)->toBeTrue()
        ->and($channel->type)->toBe('group')
        ->and($channel->participants->pluck('id')->all())->toContain($lead->id, $engineer->id);
});
