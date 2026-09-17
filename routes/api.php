<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ShiftHandoverController;
use App\Http\Controllers\Api\V1\SystemHealthController;
use App\Http\Controllers\Api\V1\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Npontu SRE Operations Platform (Version 1)
|--------------------------------------------------------------------------
| All routes under this file are prefixed by `/api/v1` and protected
| according to strict role-based and policy-based authorization rules.
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // ─── Public API Endpoints ─────────────────────────────────────────────
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::get('/health', [SystemHealthController::class, 'index'])->name('health.index');
    Route::get('/health/telemetry', [SystemHealthController::class, 'telemetry'])->name('health.telemetry');

    // ─── Protected API Endpoints (Sanctum Bearer Token Required) ──────────
    Route::middleware('auth:sanctum')->group(function (): void {

        // Session & Profile Lifecycle
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('/auth/revoke-sessions', [AuthController::class, 'revokeSessions'])->name('auth.revoke-sessions');

        // SRE Operations Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Operational Check Definitions & Status Transitions
        Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
        Route::post('/activities/bulk-assign', [ActivityController::class, 'bulkAssign'])->name('activities.bulk-assign');
        Route::get('/activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
        Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
        Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
        Route::post('/activities/{activity}/status', [ActivityController::class, 'updateStatus'])->name('activities.status');

        // Two-Way Shift Handover Protocol
        Route::get('/handovers', [ShiftHandoverController::class, 'index'])->name('handovers.index');
        Route::post('/handovers', [ShiftHandoverController::class, 'store'])->name('handovers.store');
        Route::get('/handovers/{handover}', [ShiftHandoverController::class, 'show'])->name('handovers.show');
        Route::post('/handovers/{handover}/accept', [ShiftHandoverController::class, 'accept'])->name('handovers.accept');

        // Operational Communications & Incident War Rooms
        Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
        Route::post('/conversations', [ConversationController::class, 'store'])->name('conversations.store');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
        Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');
        Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage'])->name('conversations.messages.store');
        Route::post('/conversations/{conversation}/read', [ConversationController::class, 'markAsRead'])->name('conversations.read');

        // SRE Health Subsystems & Diagnostics
        Route::get('/health/diagnostics', [SystemHealthController::class, 'diagnostics'])->name('health.diagnostics');

        // Reports & Analytics
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/handovers', [ReportController::class, 'handovers'])->name('reports.handovers');
        Route::get('/reports/timelines', [ReportController::class, 'timelines'])->name('reports.timelines');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Operational Notification Inbox & Push Status
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

        // Team Directory & Engineer Roster
        Route::get('/team', [TeamController::class, 'index'])->name('team.index');
        Route::get('/team/{user}', [TeamController::class, 'show'])->name('team.show');

        // Security Compliance Audit Trail
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
