<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_DRAFT = 1;
    public const STATUS_SCHEDULED = 2;
    public const STATUS_ACTIVE = 3;
    public const STATUS_PAUSED = 4;
    public const STATUS_COMPLETED = 5;
    public const STATUS_CANCELLED = 6;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'team_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'goals',
        'target_audience',
        'starts_at',
        'ends_at',
        'budget',
        'total_cost',
        'currency',
        'total_sent',
        'total_delivered',
        'total_failed',
        'total_unsent',
        'configuration',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'goals' => 'array',
        'target_audience' => 'array',
        'configuration' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'budget' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'total_sent' => 'decimal:2',
        'total_delivered' => 'decimal:2',
        'total_failed' => 'decimal:2',
        'total_unsent' => 'decimal:2',
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
        'status' => self::STATUS_DRAFT,
        'currency' => 'KES',
        'total_sent' => 0,
        'total_delivered' => 0,
        'total_failed' => 0,
        'total_unsent' => 0,
    ];

    /**
     * Get the user that owns the campaign.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the campaign.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the parent campaign if this is a sub-campaign.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'parent_id');
    }

    /**
     * Get the child campaigns of this campaign.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Campaign::class, 'parent_id');
    }

    /**
     * Get the communications for the campaign.
     */
    public function communications(): HasMany
    {
        return $this->hasMany(Communication::class);
    }

    /**
     * The contact groups that belong to the campaign.
     */
    public function contactGroups(): BelongsToMany
    {
        return $this->belongsToMany(ContactGroup::class, 'campaign_contact_group')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include draft campaigns.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Check if the campaign is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the campaign is a sub-campaign.
     *
     * @return bool
     */
    public function isSubCampaign(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}