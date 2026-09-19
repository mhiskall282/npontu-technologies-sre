<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * FeatureFlag — Dynamic Platform & Tenant Feature Gate Engine.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string|null $description
 * @property bool $is_enabled
 * @property array|null $target_tiers
 * @property array|null $target_org_ids
 * @property array|null $rules
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'target_tiers',
        'target_org_ids',
        'rules',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'target_tiers' => 'array',
            'target_org_ids' => 'array',
            'rules' => 'array',
        ];
    }

    /**
     * Check if this feature flag resolves as active for a given organization.
     */
    public function isEnabledFor(?Organization $org = null): bool
    {
        // Globally disabled
        if (! $this->is_enabled) {
            return false;
        }

        // If no specific targeting is set, it is globally active
        $hasTierTargeting = ! empty($this->target_tiers);
        $hasOrgTargeting = ! empty($this->target_org_ids);

        if (! $hasTierTargeting && ! $hasOrgTargeting) {
            return true;
        }

        if ($org === null) {
            return false;
        }

        // Check organization ID targeting
        if ($hasOrgTargeting && in_array($org->id, $this->target_org_ids, true)) {
            return true;
        }

        // Check tier targeting
        if ($hasTierTargeting && in_array($org->tier, $this->target_tiers, true)) {
            return true;
        }

        return false;
    }

    /**
     * Helper to quickly evaluate whether a feature key is active for an organization.
     */
    public static function isFeatureActive(string $key, ?Organization $org = null): bool
    {
        $flag = static::where('key', $key)->first();

        return $flag?->isEnabledFor($org) ?? false;
    }
}
