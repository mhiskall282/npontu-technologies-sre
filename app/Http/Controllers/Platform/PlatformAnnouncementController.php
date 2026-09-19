<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PlatformAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformAnnouncementController extends Controller
{
    /**
     * Display listing of platform operational broadcasts and notices.
     */
    public function index(): View
    {
        $announcements = PlatformAnnouncement::with('creator')
            ->latest('id')
            ->paginate(15);

        $activeCount = PlatformAnnouncement::active()->count();
        $criticalCount = PlatformAnnouncement::active()->where('type', 'critical')->count();

        return view('admin.platform.announcements.index', [
            'announcements' => $announcements,
            'activeCount' => $activeCount,
            'criticalCount' => $criticalCount,
        ]);
    }

    /**
     * Store and broadcast a new platform announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
            'type' => 'required|string|in:info,warning,critical',
            'is_active' => 'nullable|boolean',
            'dismissible' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $announcement = PlatformAnnouncement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'dismissible' => (bool) ($validated['dismissible'] ?? true),
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_name' => $request->user()?->name ?? 'Platform Admin',
            'subject_type' => PlatformAnnouncement::class,
            'subject_id' => $announcement->id,
            'event' => 'platform_announcement_created',
            'new_values' => [
                'title' => $announcement->title,
                'type' => $announcement->type,
                'is_active' => $announcement->is_active,
                'expires_at' => $announcement->expires_at?->toIso8601String(),
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        return redirect()->route('admin.platform.announcements.index')
            ->with('success', "Operational announcement '{$announcement->title}' published successfully.");
    }

    /**
     * Toggle the active broadcast status of an announcement.
     */
    public function toggle(Request $request, int $id): RedirectResponse
    {
        $announcement = PlatformAnnouncement::findOrFail($id);
        $oldState = $announcement->is_active;
        $announcement->update(['is_active' => ! $oldState]);

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_name' => $request->user()?->name ?? 'Platform Admin',
            'subject_type' => PlatformAnnouncement::class,
            'subject_id' => $announcement->id,
            'event' => 'platform_announcement_toggled',
            'old_values' => ['is_active' => $oldState],
            'new_values' => ['is_active' => ! $oldState],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        $statusLabel = $announcement->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.platform.announcements.index')
            ->with('success', "Announcement '{$announcement->title}' has been {$statusLabel}.");
    }

    /**
     * Delete an announcement.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $announcement = PlatformAnnouncement::findOrFail($id);
        $title = $announcement->title;

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_name' => $request->user()?->name ?? 'Platform Admin',
            'subject_type' => PlatformAnnouncement::class,
            'subject_id' => $announcement->id,
            'event' => 'platform_announcement_deleted',
            'old_values' => [
                'title' => $announcement->title,
                'type' => $announcement->type,
            ],
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);

        $announcement->delete();

        return redirect()->route('admin.platform.announcements.index')
            ->with('success', "Announcement '{$title}' removed permanently.");
    }
}
