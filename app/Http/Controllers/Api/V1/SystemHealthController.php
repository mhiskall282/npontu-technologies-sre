<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\SystemHealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SystemHealthController extends ApiController
{
    public function __construct(
        private readonly SystemHealthService $healthService
    ) {}

    /**
     * Standard public uptime JSON health probe.
     */
    public function index(): JsonResponse
    {
        $payload = $this->healthService->getJsonHealthPayload();

        $statusCode = ($payload['status'] === 'ok') ? 200 : 503;

        return response()->json($payload, $statusCode);
    }

    /**
     * Real-time streaming performance telemetry probe.
     */
    public function telemetry(): JsonResponse
    {
        $data = $this->healthService->getRealtimeTelemetry();

        return $this->respondWithSuccess(
            $data,
            'Real-time SRE telemetry metrics retrieved.'
        );
    }

    /**
     * Complete SRE infrastructure diagnostics and subsystem breakdown.
     */
    public function diagnostics(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! ($user->isAdmin() || $user->isLead())) {
            return $this->respondForbidden('Access restricted to SRE Leads and Administrators.');
        }

        $metrics = $this->healthService->getFullHealthMetrics();

        return $this->respondWithSuccess(
            $metrics,
            'Subsystems health diagnostics retrieved.'
        );
    }
}
