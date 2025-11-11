<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produce extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_CANCELLED = 2;
    public const STATUS_REFUNDED = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'farmer_id',
        'factory_id',
        'transaction_id',
        'trans_time',
        'trans_code',
        'route_code',
        'route_name',
        'centre_code',
        'centre_name',
        'net_units',
        'payment_rate',
        'gross_pay',
        'transport_cost',
        'transport_recovery',
        'other_charges',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trans_time' => 'datetime',
        'net_units' => 'decimal:2',
        'payment_rate' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'transport_recovery' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'status' => 'integer',
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
        'transport_cost' => 0,
        'transport_recovery' => 0,
        'other_charges' => 0,
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the farmer that owns the produce record.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the factory that owns the produce record.
     */
    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    /**
     * Scope a query to only include active produce records.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get the net payment amount after all deductions.
     *
     * @return float
     */
    public function getNetPaymentAttribute(): float
    {
        return $this->gross_pay - $this->transport_recovery - $this->other_charges;
    }
}
