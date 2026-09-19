<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlatformSecurityController extends Controller
{
    /**
     * Display the Platform Security & SIEM Audit Center.
     */
    public function index(Request $request): View
    {
        $query = SecurityEvent::with('actor')->latest('created_at');

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        if ($eventType = $request->input('event_type')) {
            $query->where('event_type', $eventType);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('event_type', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(25)->withQueryString();

        $eventTypes = SecurityEvent::select('event_type')
            ->distinct()
            ->pluck('event_type')
            ->toArray();

        return view('admin.platform.security.index', [
            'events' => $events,
            'eventTypes' => $eventTypes,
            'filters' => $request->only(['severity', 'event_type', 'search']),
        ]);
    }
}
