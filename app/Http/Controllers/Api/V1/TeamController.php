<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TeamController extends ApiController
{
    /**
     * List team directory and operator availability.
     */
    public function index(Request $request): JsonResponse
    {
        $roleFilter = $request->query('role');
        $departmentFilter = $request->query('department');
        $searchQuery = $request->query('search');

        $query = User::query()->orderBy('name', 'asc');

        if ($roleFilter !== null && $roleFilter !== '') {
            $query->where('role', $roleFilter);
        }

        if ($departmentFilter !== null && $departmentFilter !== '') {
            $query->where('department', $departmentFilter);
        }

        if ($searchQuery !== null && trim($searchQuery) !== '') {
            $term = trim($searchQuery);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('designation', 'like', "%{$term}%");
            });
        }

        $users = $query->get();

        return $this->respondWithSuccess(
            UserResource::collection($users),
            'Team directory retrieved successfully.',
            200,
            ['count' => $users->count()]
        );
    }

    /**
     * Get specific team member profile.
     */
    public function show(User $user): JsonResponse
    {
        return $this->respondWithSuccess(
            new UserResource($user),
            'Team member profile retrieved.'
        );
    }
}
