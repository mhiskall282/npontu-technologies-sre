<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $primaryOrg = $this->organizations()->first() ?? Organization::where('company_code', 'NPT-OPS-01')->first();
        $activePlan = $primaryOrg?->activePlan();
        $currentWs = $this->currentWorkspace();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'grade' => $this->grade,
            'grade_label' => User::GRADES[$this->grade] ?? $this->grade,
            'department' => $this->department,
            'designation' => $this->designation,
            'phone' => $this->phone,
            'platform_role' => $this->platform_role,
            'platform_role_label' => $this->platformRoleEnum()?->label(),
            'is_platform_admin' => $this->isPlatformAdmin(),
            'organization' => $primaryOrg ? [
                'id' => $primaryOrg->id,
                'name' => $primaryOrg->name,
                'slug' => $primaryOrg->slug,
                'company_code' => $primaryOrg->company_code,
                'tier' => $primaryOrg->tier ?? 'team',
                'status' => $primaryOrg->status ?? 'active',
                'plan_name' => $activePlan?->name ?? (ucfirst($primaryOrg->tier ?? 'Enterprise').' Plan'),
            ] : null,
            'current_workspace' => $currentWs ? [
                'id' => $currentWs->id,
                'name' => $currentWs->name,
                'slug' => $currentWs->slug,
            ] : null,
            'privileges' => $this->privileges ?? [],
            'unread_messages_count' => $this->when(
                $request->user()?->id === $this->id,
                fn () => $this->unreadMessagesCount()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
