<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Farmer extends Model
{
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
    ];

    protected $casts = [
        'can_borrow' => 'boolean',
    ];

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }

    public function produce(): HasMany
    {
        return $this->hasMany(Produce::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(FarmerWallet::class);
    }
}
