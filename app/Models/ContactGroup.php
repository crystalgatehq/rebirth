<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactGroup extends Model
{
    use HasFactory, SoftDeletes;

    // Visibility constants
    public const VISIBILITY_PRIVATE = 0;
    public const VISIBILITY_TEAM = 1;
    public const VISIBILITY_PUBLIC = 2;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'slug',
        'description',
        'visibility',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visibility' => 'integer',
        'status' => 'integer',
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
        'visibility' => self::VISIBILITY_PRIVATE,
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the user that owns the contact group.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active contact groups.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include public contact groups.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }

    /**
     * Check if the contact group is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the contact group is private.
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    /**
     * Check if the contact group can be viewed by the given user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function canBeViewedBy(User $user): bool
    {
        // Owner can always view
        if ($this->user_id === $user->id) {
            return true;
        }

        // Organization-wide visibility
        if ($this->visibility === self::VISIBILITY_ORGANIZATION) {
            return true;
        }

        // Team sharing
        if ($this->visibility === self::VISIBILITY_TEAM && !empty($this->shared_with_teams)) {
            $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
            if (count(array_intersect($this->shared_with_teams, $userTeamIds)) > 0) {
                return true;
            }
        }

        // Direct user sharing
        if (in_array($user->id, $this->shared_with_users ?? [])) {
            return true;
        }

        return false;
    }

    /**
     * Check if the contact group can be edited by the given user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        $config = $this->configuration;
        $permissions = $config['permissions']['can_edit'] ?? ['owner'];

        if (in_array('owner', $permissions) && $this->user_id === $user->id) {
            return true;
        }

        if (in_array('team', $permissions) && !empty($this->shared_with_teams)) {
            $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
            if (count(array_intersect($this->shared_with_teams, $userTeamIds)) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the subscribers for the contact group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(ContactGroupSubscriber::class);
    }
}
