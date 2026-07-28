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

class ReportController extends Controller
{
    public function __construct(private readonly ReportingService $reportingService) {}

    public function index(ReportRequest $request): View|StreamedResponse
    {
        $this->authorize('viewAny', Activity::class);

        $logs = null;
        $chartData = null;
        $activities = Activity::orderBy('title')->get(['id', 'title']);

        $validated = $request->validated();

        if (isset($validated['from']) && isset($validated['to'])) {
            if ($request->query('export') === 'csv') {
                $exportLogs = $this->reportingService->exportQuery(
                    from: $validated['from'],
                    to: $validated['to'],
                    status: $validated['status'] ?? null,
                    activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
                );

                $headers = [
                    'Content-type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename=support_activity_report_'.today()->format('Y-m-d').'.csv',
                    'Pragma' => 'no-cache',
                    'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                    'Expires' => '0',
                ];

                $callback = function () use ($exportLogs) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['Date', 'Activity Title', 'Category', 'Recurrence', 'Status', 'Updated By', 'Role', 'Remark', 'Time']);

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
                };

                return response()->stream($callback, 200, $headers);
            }

            $logs = $this->reportingService->query(
                from: $validated['from'],
                to: $validated['to'],
                status: $validated['status'] ?? null,
                activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
            );

            // Group by status
            $allMatchingLogs = $this->reportingService->exportQuery(
                from: $validated['from'],
                to: $validated['to'],
                status: $validated['status'] ?? null,
                activityId: isset($validated['activity_id']) ? (int) $validated['activity_id'] : null,
            );

            $statusCounts = $allMatchingLogs->groupBy('status')->map->count();
            $dateCounts = $allMatchingLogs->groupBy(fn ($log) => $log->date->format('Y-m-d'))->map->count()->sortKeys();

            $chartData = [
                'status' => [
                    'labels' => ['Done', 'Pending'],
                    'values' => [
                        $statusCounts->get('done', 0),
                        $statusCounts->get('pending', 0),
                    ],
                ],
                'timeline' => [
                    'labels' => $dateCounts->keys()->toArray(),
                    'values' => $dateCounts->values()->toArray(),
                ],
            ];
        }

        return view('reports.index', compact('logs', 'activities', 'chartData'));
    }

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

            $emails = User::pluck('email')->toArray();

            if (! empty($emails)) {
                Mail::to($emails)->send(
                    new ActivityReportMail($exportLogs, $validated['from'], $validated['to'])
                );
            }

            return redirect()->back()->with('success', 'Activity report successfully emailed to '.count($emails).' team members.');
        }

        return redirect()->back()->with('error', 'Please query a date range before sending the report.');
    }
}
