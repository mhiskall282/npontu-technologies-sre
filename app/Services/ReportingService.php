<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * ReportingService — Date-Range Activity Queries & Shift Aggregations
 *
 * ARCHITECTURAL DESIGN & QUERY STRATEGY:
 * 1. Primary Reporting Query:
 *    Uses a composite index on activity_logs(date, activity_id). For each (activity, date) pair,
 *    we retrieve status-change event records (append-only) so callers can derive the latest
 *    operational status from the highest ID row per group.
 *
 * 2. Eager Loading & N+1 Elimination:
 *    Always eager-loads related models (`activity`, `updater`) to ensure deterministic O(1) query
 *    execution count irrespective of page size.
 *
 * 3. Scalability & Performance:
 *    - Uses exact string match `where('date', $date)` on indexed DATE columns, avoiding unindexed `DATE()` wrappers.
 *    - Features dedicated database-level aggregation for reporting charts (`aggregateChartData`), avoiding
 *      loading thousands of models into PHP memory.
 */
final class ReportingService
{
    /**
     * Query activity logs across a date range with pagination.
     *
     * Used by the main interactive reporting screen. Returns paginated ActivityLog
     * records with activity and updater relationships pre-loaded to prevent N+1 queries.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @param  string|null  $status  Optional filter: 'pending'|'done'|null (all)
     * @param  int|null  $activityId  Optional filter for a specific activity
     * @param  int  $perPage  Records per page (default: 15)
     * @return LengthAwarePaginator Paginated collection of ActivityLog records
     */
    public function query(
        string $from,
        string $to,
        ?string $status = null,
        ?int $activityId = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        $query = ActivityLog::with(['activity', 'updater'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($activityId !== null) {
            $query->where('activity_id', $activityId);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Daily handover summary query.
     *
     * Fetches all active activities for a target date alongside ALL log entries recorded
     * for that date. Derives currentStatus from the latest log entry (append-only event model).
     *
     * @param  string  $date  Target date (Y-m-d)
     * @return Collection Collection of Activity models with derived 'current_status', 'day_logs', and 'latest_log'
     */
    public function dailySummary(string $date): Collection
    {
        return Activity::with(['logs' => function ($query) use ($date) {
            $query->where('date', $date)
                ->orderBy('id', 'asc');
        }])
            ->active()
            ->orderBy('title')
            ->get()
            ->map(function (Activity $activity) {
                $logsForDay = $activity->logs;
                $latestLog = $logsForDay->last();
                $currentStatus = $latestLog?->status ?? 'pending';

                // Attach dynamic computed attributes for the Blade/Livewire view
                $activity->setAttribute('day_logs', $logsForDay);
                $activity->setAttribute('current_status', $currentStatus);
                $activity->setAttribute('latest_log', $latestLog);

                return $activity;
            });
    }

    /**
     * Query all activity logs within a date range for export (CSV / Print / Email).
     *
     * Bypasses pagination to return the complete dataset matching the filter criteria.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @param  string|null  $status  Optional filter: 'pending'|'done'|null
     * @param  int|null  $activityId  Optional filter for a specific activity
     * @return Collection Complete collection of matching ActivityLog models
     */
    public function exportQuery(
        string $from,
        string $to,
        ?string $status = null,
        ?int $activityId = null,
    ): Collection {
        $query = ActivityLog::with(['activity', 'updater'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($activityId !== null) {
            $query->where('activity_id', $activityId);
        }

        return $query->get();
    }

    /**
     * Compute aggregated chart datasets directly in the database.
     *
     * Generates:
     *   1. Status distribution (Done vs Pending counts)
     *   2. Activity timeline (log volume grouped by day)
     *
     * PERFORMANCE NOTE:
     * Executes lean SQL GROUP BY queries instead of hydrating full Eloquent models into PHP memory.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @param  string|null  $status  Optional status filter
     * @param  int|null  $activityId  Optional activity filter
     * @return array Structure with 'status' and 'timeline' chart labels and values
     */
    public function aggregateChartData(
        string $from,
        string $to,
        ?string $status = null,
        ?int $activityId = null,
    ): array {
        $base = ActivityLog::whereBetween('date', [$from, $to]);

        if ($status !== null) {
            $base->where('status', $status);
        }

        if ($activityId !== null) {
            $base->where('activity_id', $activityId);
        }

        // Aggregate 1: Status distribution
        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Aggregate 2: Timeline volume per day
        $dateCounts = (clone $base)
            ->selectRaw('date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return [
            'status' => [
                'labels' => ['Done', 'Pending'],
                'values' => [
                    (int) ($statusCounts['done'] ?? 0),
                    (int) ($statusCounts['pending'] ?? 0),
                ],
            ],
            'timeline' => [
                'labels' => $dateCounts->keys()->map(fn ($d) => (string) $d)->toArray(),
                'values' => $dateCounts->values()->map(fn ($c) => (int) $c)->toArray(),
            ],
        ];
    }
}
