<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Mail\ActivityReportMail;
use App\Models\Activity;
use App\Models\User;
use App\Services\ReportingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ReportController — Activity Reporting, History Analysis & Export Pipeline
 *
 * Implements FR-5 (Reporting & History):
 *   - Custom date-range historical querying with status and activity filters
 *   - Aggregated operational metrics & visual chart breakdown
 *   - Multi-channel export: CSV file stream, clean browser print view, and asynchronous email dispatch
 *
 * ARCHITECTURAL DESIGN:
 * - Thin Controller: Relies on ReportRequest for input validation and ReportingService for query logic.
 * - Memory Efficiency: Large exports use streamCsv() to write directly to the output buffer in chunks.
 */
class ReportController extends Controller
{
    /**
     * Inject the ReportingService domain coordinator.
     *
     * @param  ReportingService  $reportingService  Service handling indexed reporting queries
     */
    public function __construct(private readonly ReportingService $reportingService) {}

    /**
     * Display the reporting dashboard, paginated query results, and visual analytics.
     *
     * Handles:
     *   1. Standard web request: displays filters, paginated table, and Chart.js analytics.
     *   2. CSV export query parameter: delegates to streamCsv() for immediate file download.
     *   3. Print query parameter: delegates to printView() for printer-friendly output.
     *
     * @param  ReportRequest  $request  Validated form request containing date range and filter inputs
     * @return View|StreamedResponse View with report data or streamed CSV response
     */
    public function index(ReportRequest $request): View|StreamedResponse
    {
        // Enforce authorization policy: verify user has permission to view activity reports
        $this->authorize('viewAny', Activity::class);

        $logs = null;
        $chartData = null;

        // Fetch lightweight option lists for dropdown filters (avoid fetching unnecessary columns)
        $activities = Activity::orderBy('title')->get(['id', 'title']);
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        $validated = $request->validated();

        if (isset($validated['from']) && isset($validated['to'])) {
            $from = $validated['from'];
            $to = $validated['to'];
            $status = $validated['status'] ?? null;
            $activityId = isset($validated['activity_id']) ? (int) $validated['activity_id'] : null;

            // ── Branch A: CSV Export ──────────────────────────────────────────
            if ($request->query('export') === 'csv') {
                return $this->streamCsv($validated);
            }

            // ── Branch B: Dedicated Print View (all records, clean layout) ────
            if ($request->query('print') === 'true') {
                return $this->printView($request, $validated);
            }

            // ── Branch C: Regular Paginated Interactive View ──────────────────
            // Paginated logs for the data table (prevents loading excessive records into DOM)
            $logs = $this->reportingService->query(
                from: $from,
                to: $to,
                status: $status,
                activityId: $activityId,
            );

            // Database-level chart aggregation (avoids hydrating thousands of models in PHP memory)
            $chartData = $this->reportingService->aggregateChartData(
                from: $from,
                to: $to,
                status: $status,
                activityId: $activityId,
            );
        }

        return view('reports.index', compact('logs', 'activities', 'users', 'chartData'));
    }

    /**
     * Render the printer-friendly full-record report view.
     *
     * Omits navigation bars, sidebars, and interactivity to facilitate clean paper
     * printing and PDF export via browser Print / Save to PDF.
     *
     * @param  ReportRequest  $request  Validated request
     * @param  array  $validated  Validated filter parameters
     * @return View Printable HTML view
     */
    private function printView(ReportRequest $request, array $validated): View
    {
        // Retrieve all records matching criteria without pagination
        $allLogs = $this->reportingService->exportQuery(
            from: $validated['from'],
            to: $validated['to'],
            status: $validated['status'] ?? null,
            activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
        );

        $statusCounts = $allLogs->groupBy('status')->map->count();
        $doneCount = $statusCounts->get('done', 0);
        $pendingCount = $statusCounts->get('pending', 0);
        $total = $allLogs->count();
        $completionPct = $total > 0 ? (int) round($doneCount / $total * 100) : 0;

        // Group by calendar date for clear daily sectioning in printout
        $byDate = $allLogs->groupBy(fn ($log) => $log->date->format('Y-m-d'))->sortKeys();

        $activityFilter = isset($validated['activity_id'])
            ? Activity::find((int) $validated['activity_id'])?->title
            : null;

        return view('reports.print', [
            'from' => $validated['from'],
            'to' => $validated['to'],
            'statusFilter' => $validated['status'] ?? null,
            'activityFilter' => $activityFilter,
            'byDate' => $byDate,
            'total' => $total,
            'doneCount' => $doneCount,
            'pendingCount' => $pendingCount,
            'completionPct' => $completionPct,
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now()->format('d M Y, H:i'),
        ]);
    }

    /**
     * Stream CSV export directly to the HTTP response buffer.
     *
     * Writes CSV rows sequentially using php://output to minimize memory overhead
     * when exporting large multi-month ranges.
     *
     * @param  array  $validated  Validated query parameters
     * @return StreamedResponse HTTP response with attachment headers
     */
    private function streamCsv(array $validated): StreamedResponse
    {
        $exportLogs = $this->reportingService->exportQuery(
            from: $validated['from'],
            to: $validated['to'],
            status: $validated['status'] ?? null,
            activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
        );

        $filename = 'support_activity_report_'.today()->format('Y-m-d').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($exportLogs) {
            $file = fopen('php://output', 'w');

            // Write CSV header row
            fputcsv($file, [
                'Date',
                'Activity Title',
                'Category',
                'Recurrence',
                'Status',
                'Updated By',
                'Role',
                'Remark',
                'Time',
            ]);

            // Write data rows
            foreach ($exportLogs as $log) {
                fputcsv($file, [
                    $log->date->format('Y-m-d'),
                    $log->activity?->title ?? '—',
                    $log->activity?->category ?? '—',
                    $log->activity?->recurrence ?? '—',
                    $log->status,
                    $log->actor_name,
                    $log->actor_role ?? '—',
                    $log->remark ?? '—',
                    $log->created_at->format('H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Send an activity report summary via email to designated recipients.
     *
     * @param  ReportRequest  $request  Validated form request containing recipients and date range
     * @return RedirectResponse Redirect back with success or error flash message
     */
    public function email(ReportRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', Activity::class);
        $validated = $request->validated();

        if (isset($validated['from']) && isset($validated['to'])) {
            $exportLogs = $this->reportingService->exportQuery(
                from: $validated['from'],
                to: $validated['to'],
                status: $validated['status'] ?? null,
                activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
            );

            $recipients = $validated['recipients'] ?? User::pluck('email')->toArray();

            if (! empty($recipients)) {
                Mail::to($recipients)->send(
                    new ActivityReportMail(
                        $exportLogs,
                        $validated['from'],
                        $validated['to'],
                        $validated['subject'] ?? null,
                        $validated['message'] ?? null,
                    )
                );
            }

            return redirect()->back()->with('success', 'Report emailed to '.count($recipients).' recipient(s) successfully.');
        }

        return redirect()->back()->with('error', 'Please apply a date range before sending the report.');
    }

    /**
     * SRE Shift Handover Reporting & Compliance Audit View.
     */
    public function handovers(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Activity::class);

        $from = $request->query('from', today()->subDays(30)->format('Y-m-d'));
        $to = $request->query('to', today()->format('Y-m-d'));
        $shift = $request->query('shift');
        $leadId = $request->query('lead_id') ? (int) $request->query('lead_id') : null;
        $status = $request->query('status');

        if ($request->query('export') === 'csv') {
            return $this->streamHandoverCsv($from, $to, $shift, $leadId, $status);
        }

        $handovers = $this->reportingService->handoverReportQuery(
            from: $from,
            to: $to,
            shift: $shift,
            leadId: $leadId,
            status: $status,
            perPage: $request->query('print') === 'true' ? 1000 : 15
        );

        $metrics = $this->reportingService->aggregateHandoverMetrics($from, $to);
        $leads = User::whereIn('role', ['admin', 'lead'])->orderBy('name')->get();

        return view('reports.handovers', compact('handovers', 'metrics', 'leads', 'from', 'to', 'shift', 'leadId', 'status'));
    }

    /**
     * Stream shift handovers CSV export.
     */
    private function streamHandoverCsv(string $from, string $to, ?string $shift, ?int $leadId, ?string $status): StreamedResponse
    {
        $filename = "npontu-shift-handovers-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $handovers = $this->reportingService->handoverReportQuery(
            from: $from,
            to: $to,
            shift: $shift,
            leadId: $leadId,
            status: $status,
            perPage: 5000
        );

        return response()->stream(function () use ($handovers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Handover ID',
                'Date',
                'Shift',
                'Outgoing Lead',
                'Incoming Lead',
                'Status',
                'Signed At (GMT)',
                'Accepted At (GMT)',
                'Accepted By',
                'Pending Tasks Count',
                'Completed Tasks Count',
                'Incidents Noted',
                'Summary / Notes',
                'Sign-On Acceptance Remarks',
            ]);

            foreach ($handovers as $h) {
                fputcsv($file, [
                    $h->id,
                    $h->date->format('Y-m-d'),
                    ucfirst($h->shift),
                    $h->outgoingLead?->name ?? '—',
                    $h->incomingLead?->name ?? '—',
                    $h->isAccepted() ? 'Accepted' : 'Pending Acceptance',
                    $h->signed_at?->format('Y-m-d H:i:s') ?? '—',
                    $h->accepted_at?->format('Y-m-d H:i:s') ?? '—',
                    $h->acceptedBy?->name ?? '—',
                    $h->pending_tasks_count,
                    $h->completed_tasks_count,
                    $h->incidents ?? 'None',
                    $h->summary,
                    $h->acceptance_remarks ?? 'None',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * SRE Operator Work Timelines & Duty Hours Tracking View.
     */
    public function timelines(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Activity::class);

        $from = $request->query('from', today()->subDays(14)->format('Y-m-d'));
        $to = $request->query('to', today()->format('Y-m-d'));
        $userId = $request->query('user_id') ? (int) $request->query('user_id') : null;

        if ($request->query('export') === 'csv') {
            return $this->streamTimelineCsv($from, $to, $userId);
        }

        $timelines = $this->reportingService->operatorWorkTimelinesQuery(
            from: $from,
            to: $to,
            userId: $userId
        );

        $totalHours = $timelines->sum('hours_worked');
        $totalChecks = $timelines->sum('checks_done');
        $totalEscalations = $timelines->sum('escalations');
        $avgShift = $timelines->count() > 0 ? round($totalHours / $timelines->count(), 1) : 0.0;

        $users = User::orderBy('name')->get();

        return view('reports.timelines', compact('timelines', 'totalHours', 'totalChecks', 'totalEscalations', 'avgShift', 'users', 'from', 'to', 'userId'));
    }

    /**
     * Stream operator work timelines CSV export.
     */
    private function streamTimelineCsv(string $from, string $to, ?int $userId): StreamedResponse
    {
        $filename = "npontu-operator-timelines-{$from}-to-{$to}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $timelines = $this->reportingService->operatorWorkTimelinesQuery(
            from: $from,
            to: $to,
            userId: $userId
        );

        return response()->stream(function () use ($timelines) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date',
                'Operator Name',
                'Grade',
                'Department',
                'Role',
                'First Action At (GMT)',
                'Last Action At (GMT)',
                'Estimated Hours Active',
                'Checks Completed (Done)',
                'Checks Needs Attention',
                'Total Operations Executed',
                'Escalations Flagged',
            ]);

            foreach ($timelines as $t) {
                fputcsv($file, [
                    $t['date'],
                    $t['user']->name,
                    $t['user']->grade ?? 'L2',
                    $t['user']->department ?? 'Operations',
                    $t['user']->role,
                    $t['first_action_at']->format('H:i:s'),
                    $t['last_action_at']->format('H:i:s'),
                    $t['hours_worked'],
                    $t['checks_done'],
                    $t['checks_pending'],
                    $t['total_actions'],
                    $t['escalations'],
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
