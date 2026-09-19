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
use App\Http\Controllers\OrganizationApplicationController;
use App\Http\Controllers\Platform\PlatformAnnouncementController;
use App\Http\Controllers\Platform\PlatformAuditLogController;
use App\Http\Controllers\Platform\PlatformDashboardController;
use App\Http\Controllers\Platform\PlatformFeatureFlagController;
use App\Http\Controllers\Platform\PlatformHealthController;
use App\Http\Controllers\Platform\PlatformImpersonationController;
use App\Http\Controllers\Platform\PlatformOrganizationController;
use App\Http\Controllers\Platform\PlatformPlanController;
use App\Http\Controllers\Platform\PlatformReportController;
use App\Http\Controllers\Platform\PlatformSecurityController;
use App\Http\Controllers\Platform\PlatformSettingController;
use App\Http\Controllers\Platform\PlatformSubscriptionController;
use App\Http\Controllers\Platform\PlatformUserController;
use App\Http\Controllers\Platform\PlatformWorkspaceController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkspaceController;
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

    // Multi-Tenant Workspaces & Switching
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::post('/workspaces/join', [WorkspaceController::class, 'join'])->name('workspaces.join');

    // Self-Service Organization Application
    Route::get('/organizations/apply', [OrganizationApplicationController::class, 'create'])->name('organizations.apply');
    Route::post('/organizations/apply', [OrganizationApplicationController::class, 'store'])->name('organizations.apply.store');

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

            // Platform Admin Organization Applications Review Queue
            Route::get('organizations/applications', [OrganizationApplicationController::class, 'index'])
                ->name('organizations.applications')
                ->middleware('role:admin');
            Route::post('organizations/applications/{application}/review', [OrganizationApplicationController::class, 'review'])
                ->name('organizations.applications.review')
                ->middleware('role:admin');
        });

    // ─── Platform Administration Control Plane (/admin/platform) ───────────────
    Route::get('/admin', function () {
        if (auth()->check() && auth()->user()->isPlatformAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }
        abort(403, 'Platform Administration Access Prohibited.');
    })->name('admin.entry');

    // Exit Support Impersonation Session (Restores platform admin authentication)
    Route::post('/admin/platform/impersonate/exit', [PlatformImpersonationController::class, 'exit'])->name('admin.platform.impersonate.exit');

    Route::prefix('admin/platform')
        ->name('admin.platform.')
        ->middleware(['platform.admin'])
        ->group(function () {
            // Dashboard
            Route::get('/', [PlatformDashboardController::class, 'index'])->name('dashboard');

            // Organizations Management & Impersonation
            Route::get('/organizations', [PlatformOrganizationController::class, 'index'])->name('organizations.index');
            Route::get('/organizations/{id}', [PlatformOrganizationController::class, 'show'])->name('organizations.show');
            Route::post('/organizations/{id}', [PlatformOrganizationController::class, 'update'])->name('organizations.update');
            Route::post('/organizations/{id}/suspend', [PlatformOrganizationController::class, 'suspend'])->name('organizations.suspend');
            Route::post('/organizations/{id}/reactivate', [PlatformOrganizationController::class, 'reactivate'])->name('organizations.reactivate');
            Route::put('/organizations/{id}/plan', [PlatformOrganizationController::class, 'updatePlan'])->name('organizations.update-plan');
            Route::post('/organizations/{id}/impersonate', [PlatformImpersonationController::class, 'impersonateOrganization'])->name('organizations.impersonate');

            // Users & Operators Management & Impersonation
            Route::get('/users', [PlatformUserController::class, 'index'])->name('users.index');
            Route::get('/users/{id}', [PlatformUserController::class, 'show'])->name('users.show');
            Route::match(['patch', 'put'], '/users/{id}/role', [PlatformUserController::class, 'updateRole'])->name('users.update-role');
            Route::match(['patch', 'put'], '/users/{id}/privileges', [PlatformUserController::class, 'updatePrivileges'])->name('users.update-privileges');
            Route::post('/users/{id}/suspend', [PlatformUserController::class, 'suspend'])->name('users.suspend');
            Route::post('/users/{id}/reactivate', [PlatformUserController::class, 'reactivate'])->name('users.reactivate');
            Route::post('/users/{id}/revoke-tokens', [PlatformUserController::class, 'revokeTokens'])->name('users.revoke-tokens');
            Route::post('/users/{id}/impersonate', [PlatformImpersonationController::class, 'impersonateUser'])->name('users.impersonate');

            // Workspaces Oversight
            Route::get('/workspaces', [PlatformWorkspaceController::class, 'index'])->name('workspaces.index');
            Route::get('/workspaces/{id}', [PlatformWorkspaceController::class, 'show'])->name('workspaces.show');

            // Plans & Pricing
            Route::get('/plans', [PlatformPlanController::class, 'index'])->name('plans.index');
            Route::post('/plans', [PlatformPlanController::class, 'store'])->name('plans.store');
            Route::put('/plans/{id}', [PlatformPlanController::class, 'update'])->name('plans.update');
            Route::delete('/plans/{id}', [PlatformPlanController::class, 'destroy'])->name('plans.destroy');

            // Subscriptions Ledger
            Route::get('/subscriptions', [PlatformSubscriptionController::class, 'index'])->name('subscriptions.index');
            Route::post('/subscriptions', [PlatformSubscriptionController::class, 'store'])->name('subscriptions.store');
            Route::post('/subscriptions/{id}', [PlatformSubscriptionController::class, 'update'])->name('subscriptions.update');
            Route::post('/subscriptions/{id}/status', [PlatformSubscriptionController::class, 'updateStatus'])->name('subscriptions.update-status');

            // Feature Flags & Entitlements
            Route::get('/features', [PlatformFeatureFlagController::class, 'index'])->name('features.index');
            Route::post('/features', [PlatformFeatureFlagController::class, 'store'])->name('features.store');
            Route::put('/features/{id}', [PlatformFeatureFlagController::class, 'update'])->name('features.update');
            Route::patch('/features/{id}/toggle', [PlatformFeatureFlagController::class, 'toggle'])->name('features.toggle');
            Route::delete('/features/{id}', [PlatformFeatureFlagController::class, 'destroy'])->name('features.destroy');

            // Operational Announcements & Emergency Broadcasts
            Route::get('/announcements', [PlatformAnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/announcements', [PlatformAnnouncementController::class, 'store'])->name('announcements.store');
            Route::post('/announcements/{id}/toggle', [PlatformAnnouncementController::class, 'toggle'])->name('announcements.toggle');
            Route::delete('/announcements/{id}', [PlatformAnnouncementController::class, 'destroy'])->name('announcements.destroy');

            // System Health & Operations Hub
            Route::get('/health', [PlatformHealthController::class, 'index'])->name('health.index');
            Route::post('/health/retry-jobs', [PlatformHealthController::class, 'retryFailedJobs'])->name('health.retryJobs');
            Route::post('/health/clear-cache', [PlatformHealthController::class, 'clearCache'])->name('health.clear-cache');
            Route::post('/health/prune-tokens', [PlatformHealthController::class, 'pruneTokens'])->name('health.prune-tokens');
            Route::post('/health/dispatch-reports', [PlatformHealthController::class, 'dispatchReports'])->name('health.dispatch-reports');
            Route::post('/health/diagnostics', [PlatformHealthController::class, 'runDiagnostics'])->name('health.diagnostics');

            // Security Center & SIEM
            Route::get('/security', [PlatformSecurityController::class, 'index'])->name('security.index');

            // Immutable Audit Trail
            Route::get('/audit', [PlatformAuditLogController::class, 'index'])->name('audit.index');

            // SaaS Reports & Metrics
            Route::get('/reports', [PlatformReportController::class, 'index'])->name('reports.index');

            // Enterprise Settings & Policies
            Route::get('/settings', [PlatformSettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [PlatformSettingController::class, 'update'])->name('settings.update');
        });
});

// Auth routes from Breeze (login/logout/register)
require __DIR__.'/auth.php';
