<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factory extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_MAINTENANCE = 2;
    public const STATUS_SUSPENDED = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'county_id',
        'factory_code',
        'name',
        'slug',
        'description',
        'base_url',
        'api_user',
        'api_user_credentials',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'county_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'api_user_credentials',
    ];

    /**
     * Get the county that owns the factory.
     */
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    /**
     * Get the farmers for the factory.
     */
    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class);
    }

    /**
     * Get the produce for the factory.
     */
    public function produce(): HasMany
    {
        return $this->hasMany(Produce::class);
    }

    /**
     * Get the transactions for the factory.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the farmer wallets for the factory.
     */
    public function farmerWallets(): HasMany
    {
        return $this->hasMany(FarmerWallet::class);
    }

    /**
     * Scope a query to only include active factories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
