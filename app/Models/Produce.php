<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produce extends Model
{
    protected $fillable = [
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
    ];

    protected $casts = [
        'trans_time' => 'datetime',
        'net_units' => 'decimal:2',
        'payment_rate' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'transport_cost' => 'decimal:2',
        'transport_recovery' => 'decimal:2',
        'other_charges' => 'decimal:2',
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
