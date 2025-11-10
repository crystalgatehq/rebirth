<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        '_uuid',
        'name',
        'description',
        'team_id',
        'created_by',
        'goals',
        'target_audience',
        'starts_at',
        'ends_at',
        'budget',
        'status',
        '_slug',
        'metadata'
    ];

    protected $casts = [
        'goals' => 'array',
        'target_audience' => 'array',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'budget' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->_uuid = (string) Str::uuid();
            $model->_slug = (string) Str::slug(Str::random(10) . '-' . $model->name);
        });
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function communications()
    {
        return $this->hasMany(Communication::class);
    }

    public function contactGroups()
    {
        return $this->belongsToMany(ContactGroup::class, 'campaign_contact_group')
            ->withTimestamps();
    }

    // Add scopes as needed
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(function($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', now());
            });
    }
}