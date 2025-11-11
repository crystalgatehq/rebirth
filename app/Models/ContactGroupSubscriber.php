<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactGroupSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PENDING = 2;
    public const STATUS_REJECTED = 3;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_group_subscribers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'contact_group_id',
        'farmer_id',
        'added_by',
        'status',
        'metadata',
        'last_contacted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'last_contacted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the contact group that owns the subscriber.
     */
    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(ContactGroup::class);
    }

    /**
     * Get the farmer that is subscribed.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the user who added this subscriber.
     */
    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scope a query to only include active subscribers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Mark the subscriber as contacted now.
     *
     * @return bool
     */
    public function markAsContacted(): bool
    {
        return $this->update(['last_contacted_at' => now()]);
    }

    /**
     * Check if the subscriber is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the subscriber is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the subscriber is rejected.
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}