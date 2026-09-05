<?php

declare(strict_types=1);

use App\Mail\AutomatedActivityReportMail;
use App\Models\Activity;
use App\Models\ShiftHandover;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('reports:send-automated dispatches daily SRE reports to all active users', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'email' => 'admin.test@npontu.local',
        'role' => 'admin',
        'grade' => 'L4',
    ]);
    $lead = User::factory()->create([
        'email' => 'lead.test@npontu.local',
        'role' => 'lead',
        'grade' => 'L3',
    ]);

    $activity = Activity::factory()->create(['title' => 'Gateway Heartbeat Probe']);

    $this->artisan('reports:send-automated', ['--period' => 'daily', '--date' => now()->toDateString()])
        ->assertSuccessful();

    Mail::assertSent(AutomatedActivityReportMail::class, function ($mail) use ($admin) {
        return $mail->hasTo($admin->email) && $mail->period === 'daily';
    });

    Mail::assertSent(AutomatedActivityReportMail::class, function ($mail) use ($lead) {
        return $mail->hasTo($lead->email) && $mail->period === 'daily';
    });
});

test('reports:send-automated dispatches weekly report to a specific target email', function () {
    Mail::fake();

    $this->artisan('reports:send-automated', [
        '--period' => 'weekly',
        '--email' => 'evaluator@npontu.local',
    ])->assertSuccessful();

    Mail::assertSent(AutomatedActivityReportMail::class, function ($mail) {
        return $mail->hasTo('evaluator@npontu.local') && $mail->period === 'weekly';
    });
});

test('reports:send-automated dispatches monthly report and computes metrics correctly', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'monthly.lead@npontu.local']);

    ShiftHandover::create([
        'date' => now()->toDateString(),
        'shift' => 'morning',
        'outgoing_lead_id' => $user->id,
        'summary' => 'All payment clusters nominal',
        'accepted_at' => now(),
        'accepted_by_id' => $user->id,
        'acceptance_remarks' => 'Verified database replicas',
    ]);

    $this->artisan('reports:send-automated', [
        '--period' => 'monthly',
        '--email' => 'monthly.lead@npontu.local',
    ])->assertSuccessful();

    Mail::assertSent(AutomatedActivityReportMail::class, function ($mail) {
        return $mail->period === 'monthly' &&
            $mail->metrics['handovers_count'] >= 1 &&
            $mail->metrics['uptime_sla'] === '99.98%';
    });
});

test('reports:send-automated validates period argument strictly', function () {
    $this->artisan('reports:send-automated', ['--period' => 'invalid_period'])
        ->assertFailed();
});
