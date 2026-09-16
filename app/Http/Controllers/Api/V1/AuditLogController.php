<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuditLogController extends ApiController
{
    /**
     * List immutable compliance security audit trail events.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPrivilege('view_audit_logs')) {
            return $this->respondForbidden('You do not possess the view_audit_logs privilege.');
        }

        $eventFilter = $request->query('event');
        $subjectTypeFilter = $request->query('subject_type');
        $actorIdFilter = $request->query('actor_id') ? (int) $request->query('actor_id') : null;
        $perPage = (int) $request->query('per_page', 20);

        $query = AuditLog::with('actor')->latest('created_at');

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('event', $eventFilter);
        }

        if ($subjectTypeFilter !== null && $subjectTypeFilter !== '') {
            $query->where('subject_type', 'like', "%{$subjectTypeFilter}%");
        }

        if ($actorIdFilter !== null) {
            $query->where('actor_id', $actorIdFilter);
        }

        $logs = $query->paginate($perPage);

        $meta = [
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
        ];

        return $this->respondWithSuccess(
            AuditLogResource::collection($logs->items()),
            'Security audit logs retrieved successfully.',
            200,
            $meta
        );
    }
}
