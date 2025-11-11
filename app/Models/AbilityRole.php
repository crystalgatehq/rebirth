<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbilityRole extends Model
{
    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_EXPIRED = 2;
    public const STATUS_REVOKED = 3;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ability_role';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ability_id',
        'role_id',
        'constraints',
        'granted_by',
        'grant_reason',
        'expires_at',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'constraints' => 'array',
        'expires_at' => 'datetime',
        'granted_at' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'expires_at',
        'granted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the ability that owns the ability_role.
     */
    public function ability(): BelongsTo
    {
        return $this->belongsTo(Ability::class);
    }

    /**
     * Get the role that owns the ability_role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who granted this ability.
     */
    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Check if the ability assignment is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if the ability assignment is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope a query to only include active ability assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope a query to only include expired ability assignments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('expires_at', '<=', now());
    }

    /**
     * Revoke this ability assignment.
     *
     * @param string $reason
     * @return bool
     */
    public function revoke(string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REVOKED,
            'expires_at' => now(),
            'grant_reason' => $reason ?: $this->grant_reason,
        ]);
    }
}
