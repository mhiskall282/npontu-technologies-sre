<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SecurityEvent — SIEM Security Incident & Administrative Telemetry Record.
 *
 * @property int $id
 * @property int|null $actor_id
 * @property string|null $actor_name
 * @property string $event_type
 * @property string $severity ('info' | 'warning' | 'critical')
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $details
 * @property Carbon|null $created_at
 */
class SecurityEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'event_type',
        'severity',
        'ip_address',
        'user_agent',
        'details',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * User/Operator associated with this security event.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Convenience factory method to capture and persist an immutable security event.
     *
     * @param  array<string, mixed>|null  $details
     */
    public static function record(
        string $eventType,
        string $severity = 'info',
        ?User $actor = null,
        ?array $details = null,
        ?string $ip = null,
        ?string $ua = null
    ): self {
        $ipAddress = $ip ?? request()->ip();
        $userAgent = $ua ?? (request()->userAgent() ? substr(request()->userAgent(), 0, 500) : null);

        return static::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'event_type' => $eventType,
            'severity' => $severity,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
