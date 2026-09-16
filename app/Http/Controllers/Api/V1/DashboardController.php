<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ActivityResource;
use App\Http\Resources\Api\V1\ShiftHandoverResource;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Services\ReportingService;
use App\Services\SystemHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends ApiController
{
    public function __construct(
        private readonly ReportingService $reportingService,
        private readonly SystemHealthService $healthService
    ) {}

    /**
     * Get operational SRE dashboard overview.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $todayStr = $request->query('date', today()->toDateString());

        // Derive current shift period
        $currentHour = (int) now()->format('H');
        $shiftPeriod = match (true) {
            $currentHour >= 6 && $currentHour < 14 => 'morning',
            $currentHour >= 14 && $currentHour < 22 => 'afternoon',
            default => 'night',
        };

        // All active activities with logs for target date
        $allDailyActivities = $this->reportingService->dailySummary($todayStr);

        $totalChecks = $allDailyActivities->count();
        $doneChecks = $allDailyActivities->filter(fn ($a) => $a->current_status === 'done')->count();
        $pendingChecks = $totalChecks - $doneChecks;
        $completionRate = $totalChecks > 0 ? round(($doneChecks / $totalChecks) * 100, 1) : 0.0;

        // Priority breakdown
        $criticalP1Count = $allDailyActivities->where('priority', 'critical')->count();
        $highP2Count = $allDailyActivities->where('priority', 'high')->count();
        $mediumP3Count = $allDailyActivities->where('priority', 'medium')->count();
        $lowP4Count = $allDailyActivities->where('priority', 'low')->count();

        // Active incidents for today (checks with is_escalated = true in latest log)
        $activeIncidents = $allDailyActivities
            ->filter(fn ($a) => $a->latest_log && $a->latest_log->is_escalated)
            ->values();

        // Personal queue for authenticated operator
        $assignedToMe = $allDailyActivities->where('assigned_to', $user->id)->values();
        $myPendingCount = $assignedToMe->filter(fn ($a) => $a->current_status === 'pending')->count();
        $myDoneCount = $assignedToMe->filter(fn ($a) => $a->current_status === 'done')->count();

        // Latest shift handover briefing for today
        $latestHandover = ShiftHandover::with(['outgoingLead', 'incomingLead', 'acceptedBy'])
            ->forDate($todayStr)
            ->latest('id')
            ->first();

        // System health snapshot
        $healthProbe = $this->healthService->getJsonHealthPayload();

        $data = [
            'date' => $todayStr,
            'current_shift' => $shiftPeriod,
            'metrics' => [
                'total_checks' => $totalChecks,
                'completed_checks' => $doneChecks,
                'pending_checks' => $pendingChecks,
                'completion_rate' => $completionRate,
                'critical_p1' => $criticalP1Count,
                'high_p2' => $highP2Count,
                'medium_p3' => $mediumP3Count,
                'low_p4' => $lowP4Count,
            ],
            'personal_queue' => [
                'total_assigned' => $assignedToMe->count(),
                'pending_assigned' => $myPendingCount,
                'completed_assigned' => $myDoneCount,
                'tasks' => ActivityResource::collection($assignedToMe->take(5)),
            ],
            'active_incidents_count' => $activeIncidents->count(),
            'active_incidents' => ActivityResource::collection($activeIncidents),
            'latest_handover' => $latestHandover ? new ShiftHandoverResource($latestHandover) : null,
            'unread_messages_count' => $user->unreadMessagesCount(),
            'system_health' => [
                'status' => $healthProbe['status'],
                'db_latency_ms' => $healthProbe['db_latency_ms'],
                'uptime_sla' => $healthProbe['uptime_sla'],
            ],
        ];

        return $this->respondWithSuccess($data, 'Operational dashboard summary retrieved.');
    }
}
