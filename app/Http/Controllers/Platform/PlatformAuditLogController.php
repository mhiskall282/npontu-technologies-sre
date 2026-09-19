<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformAuditLogController extends Controller
{
    /**
     * Display the Global Platform Immutable Audit Trail.
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('actor')->latest('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('actor_ip', 'like', "%{$search}%");
            });
        }

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $events = AuditLog::select('event')
            ->distinct()
            ->pluck('event')
            ->toArray();

        return view('admin.platform.audit.index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => $request->only(['search', 'event', 'from', 'to']),
        ]);
    }
}
