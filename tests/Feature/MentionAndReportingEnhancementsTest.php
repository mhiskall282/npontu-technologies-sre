<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Activities\UpdateActivityStatusAction;
use App\Livewire\OperationalChat;
use App\Mail\MessageMentionMail;
use App\Models\Activity;
use App\Models\Conversation;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('at-mention in operational chat triggers email receipt to mentioned user', function () {
    Mail::fake();

    $sender = User::factory()->create(['name' => 'Kofi Mensah', 'email' => 'kofi@npontu.com']);
    $recipient = User::factory()->create(['name' => 'Ama Osei', 'email' => 'ama@npontu.com']);

    $conversation = Conversation::create([
        'title' => 'Core Infrastructure Channel',
        'type' => 'team',
        'created_by' => $sender->id,
    ]);
    $conversation->participants()->attach([$sender->id, $recipient->id]);

    Livewire::actingAs($sender)
        ->test(OperationalChat::class)
        ->call('selectConversation', $conversation->id)
        ->set('messageText', 'Hello @Ama Osei please verify the Redis replica synchronization status.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    // Verify Mail was sent to Ama Osei
    Mail::assertSent(MessageMentionMail::class, function (MessageMentionMail $mail) use ($recipient) {
        return $mail->hasTo($recipient->email)
            && str_contains($mail->chatMessage->body, 'Redis replica synchronization');
    });
});

test('at-all broadcast triggers email notifications to all channel members except sender', function () {
    Mail::fake();

    $sender = User::factory()->create(['name' => 'Lead Engineer', 'email' => 'lead@npontu.com']);
    $member1 = User::factory()->create(['name' => 'SRE 1', 'email' => 'sre1@npontu.com']);
    $member2 = User::factory()->create(['name' => 'SRE 2', 'email' => 'sre2@npontu.com']);

    $conversation = Conversation::create([
        'title' => 'SRE Incident Response',
        'type' => 'team',
        'created_by' => $sender->id,
    ]);
    $conversation->participants()->attach([$sender->id, $member1->id, $member2->id]);

    Livewire::actingAs($sender)
        ->test(OperationalChat::class)
        ->call('selectConversation', $conversation->id)
        ->call('insertMention', '@all')
        ->assertSet('messageText', '@all ')
        ->set('messageText', '@all Critical: database failover scheduled in 10 minutes.')
        ->call('sendMessage')
        ->assertHasNoErrors();

    Mail::assertSent(MessageMentionMail::class, function (MessageMentionMail $mail) use ($member1) {
        return $mail->hasTo($member1->email) && $mail->isBroadcast === true;
    });

    Mail::assertSent(MessageMentionMail::class, function (MessageMentionMail $mail) use ($member2) {
        return $mail->hasTo($member2->email) && $mail->isBroadcast === true;
    });

    // Sender should NOT receive their own broadcast
    Mail::assertNotSent(MessageMentionMail::class, function (MessageMentionMail $mail) use ($sender) {
        return $mail->hasTo($sender->email);
    });
});

test('shift handover reporting query renders page with metrics and handover rows', function () {
    $lead1 = User::factory()->create(['name' => 'Lead Kwame', 'role' => 'lead']);
    $lead2 = User::factory()->create(['name' => 'Lead Abena', 'role' => 'lead']);

    ShiftHandover::create([
        'date' => today()->toDateString(),
        'shift' => 'morning',
        'outgoing_lead_id' => $lead1->id,
        'incoming_lead_id' => $lead2->id,
        'summary' => 'Smooth operational shift, payment gateways verified.',
        'incidents' => 'None',
        'pending_tasks_count' => 0,
        'completed_tasks_count' => 15,
        'accepted_at' => now(),
        'acceptance_remarks' => 'Confirmed and verified.',
    ]);

    $response = $this->actingAs($lead1)->get(route('reports.handovers', [
        'from' => today()->subDay()->toDateString(),
        'to' => today()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertSee('SRE Shift Handover Reporting');
    $response->assertSee('Smooth operational shift, payment gateways verified.');
    $response->assertSee('Export Handover CSV');
});

test('shift handover CSV export streams valid CSV with correct headers', function () {
    $lead1 = User::factory()->create(['name' => 'Lead Kwame', 'role' => 'lead']);
    $lead2 = User::factory()->create(['name' => 'Lead Abena', 'role' => 'lead']);

    ShiftHandover::create([
        'date' => today()->toDateString(),
        'shift' => 'afternoon',
        'outgoing_lead_id' => $lead1->id,
        'incoming_lead_id' => $lead2->id,
        'summary' => 'Afternoon shift turnover with minor DNS alert.',
        'incidents' => 'DNS query timeout on node 3',
        'pending_tasks_count' => 2,
        'completed_tasks_count' => 18,
    ]);

    $response = $this->actingAs($lead1)->get(route('reports.handovers', [
        'from' => today()->subDays(2)->toDateString(),
        'to' => today()->toDateString(),
        'export' => 'csv',
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->headers->get('content-disposition'))->toContain('npontu-shift-handovers-');
});

test('operator work timelines report calculates duty hours and renders audited rows', function () {
    $user = User::factory()->create([
        'name' => 'Efe Boateng',
        'department' => 'Site Reliability Engineering',
        'grade' => 'L4',
    ]);
    $activity = Activity::factory()->create();

    // Generate activity log for this user today
    $this->actingAs($user);
    $action = app(UpdateActivityStatusAction::class);
    $action->execute($activity, 'done', 'Completed morning server inspection', today()->toDateString());

    $response = $this->actingAs($user)->get(route('reports.timelines', [
        'from' => today()->toDateString(),
        'to' => today()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertSee('Operator Work Timelines');
    $response->assertSee('Efe Boateng');
    $response->assertSee('L4');
    $response->assertSee('Site Reliability Engineering');
    $response->assertSee('Export Timelines CSV');
});

test('operator work timelines CSV export streams properly', function () {
    $user = User::factory()->create([
        'name' => 'Efe Boateng',
        'department' => 'DevOps',
        'grade' => 'L3',
    ]);

    $response = $this->actingAs($user)->get(route('reports.timelines', [
        'from' => today()->subDays(3)->toDateString(),
        'to' => today()->toDateString(),
        'export' => 'csv',
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->headers->get('content-disposition'))->toContain('npontu-operator-timelines-');
});
