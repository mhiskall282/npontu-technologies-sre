<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * OrganizationApplication — Self-Service Organization Registration & Approval Lifecycle.
 *
 * @property int $id
 * @property string $organization_name
 * @property string $organization_slug
 * @property int $applicant_user_id
 * @property string $contact_email
 * @property string $status ('pending_review' | 'approved' | 'rejected' | 'additional_info_required')
 * @property string $deployment_model
 * @property string $preferred_region
 * @property string $tier
 * @property string|null $notes
 * @property string|null $review_notes
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property string|null $rejection_reason
 * @property int $risk_score
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class OrganizationApplication extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_name',
        'organization_slug',
        'applicant_user_id',
        'contact_email',
        'status',
        'deployment_model',
        'preferred_region',
        'tier',
        'notes',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'risk_score',
        'metadata',
    ];

    /**
     * Attribute type casting.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'risk_score' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * The applicant user who submitted this registration request.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    /**
     * The platform administrator who reviewed this application.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The resulting organization entity if created/approved.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_slug', 'slug');
    }

    /**
     * Check if application is pending manual review.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending_review';
    }

    /**
     * Check if application is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
