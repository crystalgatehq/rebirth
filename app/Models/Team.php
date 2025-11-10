<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;
    public const STATUS_SUSPENDED = 2;

    // Role constants
    public const OWNER = 'owner';
    public const MEMBER = 'member';

    // Team types
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_PROJECT = 'project';
    public const TYPE_TEAM = 'team';
    public const TYPE_ORGANIZATION = 'organization';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'name',
        'display_name',
        '_slug',
        'code',
        'description',
        'owner_id',
        'parent_team_id',
        'logo_path',
        'banner_path',
        'website',
        'industry',
        'size',
        'settings',
        'communication_settings',
        'type',
        'is_active',
        'is_verified',
        'verified_at',
        'verified_by',
        '_status',
        'trial_ends_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'communication_settings' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'size' => 'integer',
        '_status' => 'integer',
    ];

    protected $dates = [
        'verified_at',
        'trial_ends_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function parentTeam()
    {
        return $this->belongsTo(Team::class, 'parent_team_id');
    }

    public function childTeams()
    {
        return $this->hasMany(Team::class, 'parent_team_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot([
                'role',
                'permissions',
                'metadata',
                'temporal',
                '_status',
                'status_reason',
                'status_changed_at',
                'status_changed_by',
                'added_by',
                'notes'
            ])
            ->withTimestamps()
            ->withTrashed();
    }

    public function contactGroups()
    {
        return $this->hasMany(ContactGroup::class);
    }

    public function addMember(User $user, string $role = self::MEMBER, ?int $addedBy = null)
    {
        return $this->members()->syncWithoutDetaching([
            $user->id => [
                'role' => $role,
                'added_by' => $addedBy ?? auth()->id(),
                '_status' => self::STATUS_ACTIVE,
                'status_changed_at' => now(),
                'status_changed_by' => $addedBy ?? auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ], false);
    }

    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('_status', self::STATUS_ACTIVE)
            ->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)
            ->whereNotNull('verified_at');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithActiveMembers($query)
    {
        return $query->whereHas('members', function ($q) {
            $q->where('_status', self::STATUS_ACTIVE);
        });
    }

    public function getSettingsAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }

    public function getCommunicationSettingsAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [];
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner_path ? asset('storage/' . $this->banner_path) : null;
    }

    protected static function booted()
    {
        static::creating(function ($team) {
            if (empty($team->code)) {
                $team->code = strtoupper(substr($team->name, 0, 4));
            }
            if (empty($team->_slug)) {
                $team->_slug = \Illuminate\Support\Str::slug($team->name, '_');
            }
            if (empty($team->display_name)) {
                $team->display_name = $team->name;
            }
        });
    }
}