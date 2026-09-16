<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Handovers\AcceptShiftHandoverAction;
use App\Actions\Handovers\CreateShiftHandoverAction;
use App\Http\Requests\Api\V1\AcceptShiftHandoverApiRequest;
use App\Http\Requests\Api\V1\StoreShiftHandoverApiRequest;
use App\Http\Resources\Api\V1\ShiftHandoverResource;
use App\Models\ShiftHandover;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShiftHandoverController extends ApiController
{
    public function __construct(
        private readonly ReportingService $reportingService
    ) {}

    /**
     * List formal SRE shift handover briefings.
     */
    public function index(Request $request): JsonResponse
    {
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
            'Shift handovers retrieved successfully.',
            200,
            $meta
        );
    }

    /**
     * Author and digitally sign off an outgoing shift handover briefing.
     */
    public function store(
        StoreShiftHandoverApiRequest $request,
        CreateShiftHandoverAction $action
    ): JsonResponse {
        $validated = $request->validated();

        // If counts are not supplied, calculate automatically from daily summary
        if (! isset($validated['pending_tasks_count']) || ! isset($validated['completed_tasks_count'])) {
            $summary = $this->reportingService->dailySummary($validated['date']);
            $validated['completed_tasks_count'] = $summary->filter(fn ($a) => $a->current_status === 'done')->count();
            $validated['pending_tasks_count'] = $summary->count() - $validated['completed_tasks_count'];
        }

        $handover = $action->execute($validated);

        return $this->respondCreated(
            new ShiftHandoverResource($handover->load(['outgoingLead', 'incomingLead', 'acceptedBy'])),
            'Shift handover briefing signed off successfully.'
        );
    }

    /**
     * Get shift handover details.
     */
    public function show(ShiftHandover $handover): JsonResponse
    {
        $handover->load(['outgoingLead', 'incomingLead', 'acceptedBy']);

        return $this->respondWithSuccess(
            new ShiftHandoverResource($handover),
            'Shift handover retrieved successfully.'
        );
    }

    /**
     * Acknowledge and formally accept operational responsibility (Incoming Lead sign-on).
     */
    public function accept(
        AcceptShiftHandoverApiRequest $request,
        ShiftHandover $handover,
        AcceptShiftHandoverAction $action
    ): JsonResponse {
        if ($handover->isAccepted()) {
            return $this->respondWithError('This shift handover has already been accepted and locked.', 409);
        }

        $remarks = $request->validated()['remarks'] ?? null;
        $handover = $action->execute($handover, $remarks);

        return $this->respondWithSuccess(
            new ShiftHandoverResource($handover->load(['outgoingLead', 'incomingLead', 'acceptedBy'])),
            'Shift handover responsibility formally accepted.'
        );
    }
}
