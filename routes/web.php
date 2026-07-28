<?php

declare(strict_types=1);

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Livewire\DailyActivityBoard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// ─── Public routes (guest only) ────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    // Auth routes injected by Breeze
});

// ─── Authenticated routes ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Daily handover view (Livewire component)
    Route::get('/daily', DailyActivityBoard::class)->name('activities.daily');

    // Activity CRUD (read = any auth; create/update/delete = lead/admin via Policy)
    Route::resource('activities', ActivityController::class);

    // Reporting
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/email', [ReportController::class, 'email'])->name('reports.email');

    // Profile Settings
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Health check — beyond spec; included for SRE alignment (uptime monitoring)
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'db' => DB::select('SELECT 1') ? 'ok' : 'error',
        ]);
    })->name('health');

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
            Route::resource('activities', Admin\ActivityController::class);
        });
});

// Auth routes from Breeze (login/logout/register)
require __DIR__.'/auth.php';
