<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factory extends Model
{
    protected $fillable = [
        'factory_code',
        'name',
        'base_url',
    ];

    public function farmers(): HasMany
    {
        return $this->hasMany(Farmer::class);
    }

    public function produce(): HasMany
    {
        return $this->hasMany(Produce::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function farmerWallets(): HasMany
    {
        return $this->hasMany(FarmerWallet::class);
    }
}
