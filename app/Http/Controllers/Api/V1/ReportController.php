<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Http\Resources\Api\V1\ShiftHandoverResource;
use App\Models\User;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends ApiController
{
    public function __construct(
        private readonly ReportingService $reportingService
    ) {}

    /**
     * Query activity checkoff history across date range with status filters and aggregated chart data.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPrivilege('export_reports')) {
            return $this->respondForbidden('You do not possess the export_reports privilege.');
        }

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', today()->toDateString());
        $status = $request->query('status');
        $activityId = $request->query('activity_id') ? (int) $request->query('activity_id') : null;
        $perPage = (int) $request->query('per_page', 15);

        $paginatedLogs = $this->reportingService->query(
            from: $from,
            to: $to,
            status: $status,
            activityId: $activityId,
            perPage: $perPage
        );

        $chartData = $this->reportingService->aggregateChartData(
            from: $from,
            to: $to,
            status: $status,
            activityId: $activityId
        );

        $meta = [
            'from' => $from,
            'to' => $to,
            'status_filter' => $status,
            'charts' => $chartData,
            'current_page' => $paginatedLogs->currentPage(),
            'last_page' => $paginatedLogs->lastPage(),
            'per_page' => $paginatedLogs->perPage(),
            'total' => $paginatedLogs->total(),
        ];

        return $this->respondWithSuccess(
            ActivityLogResource::collection($paginatedLogs->items()),
            'Activity reports retrieved successfully.',
            200,
            $meta
        );
    }

    /**
     * Handover compliance report across date range.
     */
    public function handovers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPrivilege('export_reports')) {
            return $this->respondForbidden('You do not possess the export_reports privilege.');
        }

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', today()->toDateString());
        $shift = $request->query('shift');
        $status = $request->query('status');
        $leadId = $request->query('lead_id') ? (int) $request->query('lead_id') : null;
        $perPage = (int) $request->query('per_page', 15);

        $handovers = $this->reportingService->handoverReportQuery(
            from: $from,
            to: $to,
            shift: $shift,
            leadId: $leadId,
            status: $status,
            perPage: $perPage
        );

        $metrics = $this->reportingService->aggregateHandoverMetrics($from, $to);

        $meta = [
            'from' => $from,
            'to' => $to,
            'metrics' => $metrics,
            'current_page' => $handovers->currentPage(),
            'last_page' => $handovers->lastPage(),
            'per_page' => $handovers->perPage(),
            'total' => $handovers->total(),
        ];

        return $this->respondWithSuccess(
            ShiftHandoverResource::collection($handovers->items()),
            'Shift handover report retrieved.',
            200,
            $meta
        );
    }

    /**
     * Operator working hours and timeline analysis.
     */
    public function timelines(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPrivilege('export_reports')) {
            return $this->respondForbidden('You do not possess the export_reports privilege.');
        }

        $from = $request->query('from', now()->subDays(14)->toDateString());
        $to = $request->query('to', today()->toDateString());
        $userId = $request->query('user_id') ? (int) $request->query('user_id') : null;

        $timelines = $this->reportingService->operatorWorkTimelinesQuery($from, $to, $userId);

        $formatted = $timelines->map(function ($item) {
            return [
                'date' => $item['date'],
                'user_id' => $item['user_id'],
                'operator_name' => $item['user']->name ?? 'Unknown',
                'operator_role' => $item['user']->role ?? 'agent',
                'first_action_at' => $item['first_action_at']?->toIso8601String(),
                'last_action_at' => $item['last_action_at']?->toIso8601String(),
                'hours_worked' => $item['hours_worked'],
                'checks_done' => $item['checks_done'],
                'checks_pending' => $item['checks_pending'],
                'total_actions' => $item['total_actions'],
                'escalations' => $item['escalations'],
            ];
        });

        return $this->respondWithSuccess(
            $formatted,
            'Operator timelines retrieved successfully.',
            200,
            ['from' => $from, 'to' => $to, 'total_entries' => $formatted->count()]
        );
    }

    /**
     * Export raw dataset for CSV download.
     */
    public function export(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPrivilege('export_reports')) {
            return $this->respondForbidden('You do not possess the export_reports privilege.');
        }

        $from = $request->query('from', now()->subDays(30)->toDateString());
        $to = $request->query('to', today()->toDateString());
        $status = $request->query('status');
        $activityId = $request->query('activity_id') ? (int) $request->query('activity_id') : null;

        $logs = $this->reportingService->exportQuery(
            from: $from,
            to: $to,
            status: $status,
            activityId: $activityId
        );

        return $this->respondWithSuccess(
            ActivityLogResource::collection($logs),
            'Raw activity export records retrieved.',
            200,
            ['from' => $from, 'to' => $to, 'count' => $logs->count()]
        );
    }
}
