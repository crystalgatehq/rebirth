<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'farmer_id',
        'factory_id',
        'amount',
        'charge',
        'convenience_fee',
        'interest',
        'total_amount',
        'status',
        'description',
        'system',
        'loan_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge' => 'decimal:2',
        'convenience_fee' => 'decimal:2',
        'interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'loan_date' => 'datetime',
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
