<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Farmer extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_SUSPENDED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'factory_id',
        'farmer_code',
        'can_borrow',
        'centre_code',
        'centre_name',
        'id_number',
        'name',
        'phone',
        'route_code',
        'route_name',
        'slug',
        'description',
        'configuration',
        'status',
    ];

    /**
     * The model's default configuration values.
     *
     * @var array
     */
    protected $attributes = [
        'configuration' => '{"sms":true,"email":false,"whatsapp":false}',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'can_borrow' => 'boolean',
        'configuration' => 'array',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the factory that owns the farmer.
     */
    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    /**
     * Get the produce for the farmer.
     */
    public function produce(): HasMany
    {
        return $this->hasMany(Produce::class);
    }

    /**
     * Get the transactions for the farmer.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the wallet associated with the farmer.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(FarmerWallet::class);
    }

    /**
     * Scope a query to only include active farmers.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
