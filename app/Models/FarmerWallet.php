<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerWallet extends Model
{
    protected $fillable = [
        'farmer_id',
        'factory_id',
        'balance',
        'loan_limit',
        'borrowed_amount',
        'available_earnings',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'loan_limit' => 'decimal:2',
        'borrowed_amount' => 'decimal:2',
        'available_earnings' => 'decimal:2',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function factory(): BelongsTo
    {
        return $this->belongsTo(Factory::class);
    }
}
