<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\EmailReplyController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Livewire\DailyActivityBoard;
use App\Livewire\OperationalChat;
use Illuminate\Support\Facades\Route;

// ─── Public routes (accessible by visitors & teams) ─────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/docs', [DocsController::class, 'index'])->name('docs');

// ─── Instant Email Reply Bridge & Inbound Webhook ──────────────────────────
Route::get('/messages/reply/{token}', [EmailReplyController::class, 'show'])->name('messages.email_reply.show');
Route::post('/messages/reply/{token}', [EmailReplyController::class, 'store'])->name('messages.email_reply.store');
Route::post('/api/webhooks/inbound-email', [EmailReplyController::class, 'inbound'])->name('webhooks.inbound_email');

// ─── Public SRE Policies & Legal Compliance ────────────────────────────────
Route::get('/privacy-policy', [PolicyController::class, 'privacy'])->name('policy.privacy');
Route::get('/terms-of-service', [PolicyController::class, 'terms'])->name('policy.terms');
Route::get('/security-policy', [PolicyController::class, 'security'])->name('policy.security');
Route::get('/sla-commitment', [PolicyController::class, 'sla'])->name('policy.sla');

Route::middleware('guest')->group(function () {
    // Auth routes injected by Breeze
});

// ─── Public health check & SRE Status Dashboard ─────────────────────────────
Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/health/telemetry', [HealthController::class, 'telemetry'])->name('health.telemetry');

// ─── Authenticated routes ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Primary gateway redirect (sends authenticated operators to /daily)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Daily handover view (Livewire component)
    Route::get('/daily', DailyActivityBoard::class)->name('activities.daily');

    // SRE Operational Messaging & Channels (Livewire component)
    Route::get('/messages', OperationalChat::class)->name('messages.index');

    // Activity CRUD (read = any auth; create/update/delete = lead/admin via Policy)
    Route::resource('activities', ActivityController::class);

    // Reporting & Compliance Analytics
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/handovers', [ReportController::class, 'handovers'])->name('reports.handovers');
    Route::get('/reports/timelines', [ReportController::class, 'timelines'])->name('reports.timelines');
    Route::post('/reports/email', [ReportController::class, 'email'])->name('reports.email');

    // Profile Settings
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // SRE Monitoring (admin + lead)
    Route::get('/monitoring', [MonitoringController::class, 'index'])
        ->name('monitoring.index')
        ->middleware('role:admin,lead');

    // Admin-only routes
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin,lead')
        ->group(function () {
            Route::resource('users', Admin\UserController::class)->middleware('role:admin');
            // Admin-initiated password reset (POST to avoid GET bookmarking)
            Route::post('users/{user}/reset-password', [Admin\UserController::class, 'resetPassword'])
                ->name('users.resetPassword')
                ->middleware('role:admin');
            Route::resource('activities', Admin\ActivityController::class);
        });
});

// Auth routes from Breeze (login/logout/register)
require __DIR__.'/auth.php';
