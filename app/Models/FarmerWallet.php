<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FarmerWallet extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_SUSPENDED = 2;
    public const STATUS_FROZEN = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'farmer_id',
        'factory_id',
        'balance',
        'loan_limit',
        'borrowed_amount',
        'available_earnings',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'loan_limit' => 'decimal:2',
        'borrowed_amount' => 'decimal:2',
        'available_earnings' => 'decimal:2',
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
        'balance' => 0,
        'loan_limit' => 0,
        'borrowed_amount' => 0,
        'available_earnings' => 0,
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the farmer that owns the wallet.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the factory that owns the wallet.
     */
    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    /**
     * Scope a query to only include active wallets.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Get the available credit (loan limit - borrowed amount).
     *
     * @return float
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->loan_limit - $this->borrowed_amount);
    }

    /**
     * Get the total balance (balance + available_earnings).
     *
     * @return float
     */
    public function getTotalBalanceAttribute(): float
    {
        return $this->balance + $this->available_earnings;
    }
}
