<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\ShiftHandover;
use App\Models\User;
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
        $query = ActivityLog::with(['activity.assignee', 'updater'])
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
        return Activity::with([
            'assignee',
            'logs' => function ($query) use ($date) {
                $query->where('date', $date)
                    ->orderBy('id', 'asc');
            },
        ])
            ->active()
            ->orderByDesc('is_pinned')
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
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

    /**
     * Query formal shift handovers across a date range with optional filtering.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @param  string|null  $shift  Optional shift filter ('morning'|'afternoon'|'night')
     * @param  int|null  $leadId  Optional lead user filter
     * @param  string|null  $status  Optional acceptance status filter ('accepted'|'pending')
     * @param  int  $perPage  Records per page
     */
    public function handoverReportQuery(
        string $from,
        string $to,
        ?string $shift = null,
        ?int $leadId = null,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = ShiftHandover::with(['outgoingLead', 'incomingLead', 'acceptedBy'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        if ($shift !== null && $shift !== '') {
            $query->where('shift', $shift);
        }

        if ($leadId !== null) {
            $query->where(function ($q) use ($leadId) {
                $q->where('outgoing_lead_id', $leadId)
                    ->orWhere('incoming_lead_id', $leadId)
                    ->orWhere('accepted_by_id', $leadId);
            });
        }

        if ($status === 'accepted') {
            $query->whereNotNull('accepted_at');
        } elseif ($status === 'pending') {
            $query->whereNull('accepted_at');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Compute aggregated compliance KPIs for shift handovers across a date range.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @return array<string, mixed>
     */
    public function aggregateHandoverMetrics(string $from, string $to): array
    {
        $handovers = ShiftHandover::whereBetween('date', [$from, $to])->get();

        $total = $handovers->count();
        $accepted = $handovers->filter(fn ($h) => $h->isAccepted())->count();
        $pending = $total - $accepted;
        $acceptanceRate = $total > 0 ? round(($accepted / $total) * 100, 1) : 100.0;
        $incidentsCount = $handovers->filter(fn ($h) => ! empty($h->incidents))->count();

        return [
            'total' => $total,
            'accepted' => $accepted,
            'pending' => $pending,
            'acceptance_rate' => $acceptanceRate,
            'incidents_count' => $incidentsCount,
        ];
    }

    /**
     * Compute operator shift activity timelines and active hours worked.
     *
     * Derives working intervals from append-only activity logs and shift handover signatures.
     *
     * @param  string  $from  Start date (Y-m-d)
     * @param  string  $to  End date (Y-m-d)
     * @param  int|null  $userId  Optional user filter
     * @return Collection<int, array<string, mixed>>
     */
    public function operatorWorkTimelinesQuery(string $from, string $to, ?int $userId = null): Collection
    {
        // Query activity logs within range
        $logsQuery = ActivityLog::with('updater')
            ->whereBetween('date', [$from, $to]);

        if ($userId !== null) {
            $logsQuery->where('updated_by', $userId);
        }

        $logs = $logsQuery->get();

        // Group by [date, updated_by]
        $grouped = [];

        foreach ($logs as $log) {
            if (! $log->updated_by) {
                continue;
            }

            $key = "{$log->date->format('Y-m-d')}_{$log->updated_by}";

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $log->date->format('Y-m-d'),
                    'user' => $log->updater,
                    'user_id' => $log->updated_by,
                    'timestamps' => [],
                    'checks_done' => 0,
                    'checks_pending' => 0,
                    'escalations' => 0,
                ];
            }

            $grouped[$key]['timestamps'][] = $log->created_at;

            if ($log->status === 'done') {
                $grouped[$key]['checks_done']++;
            } else {
                $grouped[$key]['checks_pending']++;
            }

            if ($log->is_escalated) {
                $grouped[$key]['escalations']++;
            }
        }

        // Include shift handovers
        $handoversQuery = ShiftHandover::with(['outgoingLead', 'acceptedBy'])
            ->whereBetween('date', [$from, $to]);

        $handovers = $handoversQuery->get();

        foreach ($handovers as $handover) {
            $d = $handover->date->format('Y-m-d');

            if ($handover->outgoing_lead_id) {
                $uId = $handover->outgoing_lead_id;
                if ($userId === null || $userId === $uId) {
                    $k = "{$d}_{$uId}";
                    if (! isset($grouped[$k])) {
                        $grouped[$k] = [
                            'date' => $d,
                            'user' => $handover->outgoingLead,
                            'user_id' => $uId,
                            'timestamps' => [],
                            'checks_done' => 0,
                            'checks_pending' => 0,
                            'escalations' => 0,
                        ];
                    }
                    if ($handover->signed_at) {
                        $grouped[$k]['timestamps'][] = $handover->signed_at;
                    }
                }
            }

            if ($handover->accepted_by_id) {
                $uId = $handover->accepted_by_id;
                if ($userId === null || $userId === $uId) {
                    $k = "{$d}_{$uId}";
                    if (! isset($grouped[$k])) {
                        $grouped[$k] = [
                            'date' => $d,
                            'user' => $handover->acceptedBy,
                            'user_id' => $uId,
                            'timestamps' => [],
                            'checks_done' => 0,
                            'checks_pending' => 0,
                            'escalations' => 0,
                        ];
                    }
                    if ($handover->accepted_at) {
                        $grouped[$k]['timestamps'][] = $handover->accepted_at;
                    }
                }
            }
        }

        // Process hours and timeline summary for each record
        $results = collect();

        foreach ($grouped as $entry) {
            if (empty($entry['timestamps']) || ! $entry['user']) {
                continue;
            }

            usort($entry['timestamps'], fn ($a, $b) => $a <=> $b);

            $firstAt = $entry['timestamps'][0];
            $lastAt = end($entry['timestamps']);

            // Calculate duration in minutes between first and last recorded operational action
            $diffMinutes = (int) $firstAt->diffInMinutes($lastAt);

            // Minimum baseline shift window: if an operator only made one quick check, estimate 0.5 hr
            if ($diffMinutes < 30) {
                $hoursWorked = 0.5;
            } else {
                $hoursWorked = round($diffMinutes / 60, 1);
            }

            $results->push([
                'date' => $entry['date'],
                'user' => $entry['user'],
                'first_action_at' => $firstAt,
                'last_action_at' => $lastAt,
                'hours_worked' => $hoursWorked,
                'checks_done' => $entry['checks_done'],
                'checks_pending' => $entry['checks_pending'],
                'total_actions' => count($entry['timestamps']),
                'escalations' => $entry['escalations'],
            ]);
        }

        return $results->sortByDesc('date')->values();
    }
}
