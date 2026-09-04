<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Mail\ActivityReportMail;
use App\Models\Activity;
use App\Models\User;
use App\Services\ReportingService;
use Illuminate\Http\RedirectResponse;
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
}
