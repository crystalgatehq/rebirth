<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactGroupSubscriber extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PENDING = 2;
    const STATUS_REJECTED = 3;

    protected $table = 'contact_group_subscribers';

    protected $fillable = [
        'contact_group_id',
        'farmer_id',
        'added_by',
        '_status',
        'metadata',
        'last_contacted_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_contacted_at' => 'datetime'
    ];

    public function contactGroup()
    {
        return $this->belongsTo(ContactGroup::class);
    }

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeActive($query)
    {
        return $query->where('_status', self::STATUS_ACTIVE);
    }

    public function markAsContacted()
    {
        $this->update(['last_contacted_at' => now()]);
    }
}