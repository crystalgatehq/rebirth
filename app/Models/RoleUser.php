<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleUser extends Model
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
    protected $table = 'role_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'user_id',
        'constraints',
        'assigned_by',
        'assignment_notes',
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
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'assigned_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the role that owns the role_user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user that owns the role_user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who assigned this role.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if the role assignment is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if the role assignment is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope a query to only include active role assignments.
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
     * Scope a query to only include expired role assignments.
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
     * Revoke this role assignment.
     *
     * @param string $notes
     * @return bool
     */
    public function revoke(string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REVOKED,
            'expires_at' => now(),
            'assignment_notes' => $notes ?: $this->assignment_notes,
        ]);
    }

    /**
     * Extend the expiration date of this role assignment.
     *
     * @param \DateTimeInterface|string $expiresAt
     * @return bool
     */
    public function extend($expiresAt): bool
    {
        return $this->update(['expires_at' => $expiresAt]);
    }
}
