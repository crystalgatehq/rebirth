<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ContactGroup extends Model
{
    use HasFactory, SoftDeletes;

    const VISIBILITY_PRIVATE = 'PRIVATE';
    const VISIBILITY_TEAM = 'TEAM';
    const VISIBILITY_ORGANIZATION = 'ORGANIZATION';

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    protected $fillable = [
        'user_id', 'name', '_slug', 'description', 'visibility',
        'shared_with_teams', 'shared_with_users', 'configuration', '_status'
    ];

    protected $casts = [
        'shared_with_teams' => 'array',
        'shared_with_users' => 'array',
        'configuration' => 'array',
        '_status' => 'integer',
    ];

    protected $attributes = [
        'visibility' => self::VISIBILITY_PRIVATE,
        '_status' => self::STATUS_ACTIVE,
        'shared_with_teams' => '[]',
        'shared_with_users' => '[]',
        'configuration' => '{"delivery":{"type":"IMMEDIATE","time":"09:00","timezone":"Africa\/Nairobi","frequency":"ONCE","days":[1,2,3,4,5]},"notifications":{"on_send":true,"on_delivery":true,"on_failure":true},"permissions":{"can_edit":["owner"],"can_send":["owner","team"],"can_manage_members":["owner"]}}'
    ];

    protected static function booted()
    {
        static::creating(function ($contactGroup) {
            if (Auth::check()) {
                $contactGroup->user_id = $contactGroup->user_id ?? Auth::id();
            }
            $contactGroup->_slug = \Illuminate\Support\Str::slug($contactGroup->name);
        });

        static::updating(function ($contactGroup) {
            if ($contactGroup->isDirty('name')) {
                $contactGroup->_slug = \Illuminate\Support\Str::slug($contactGroup->name);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members()
    {
        return $this->hasMany(ContactGroupMember::class);
    }

    public function communications()
    {
        return $this->belongsToMany(Communication::class, 'communication_contact_group');
    }

    public function shareWithUsers(array $userIds)
    {
        $current = $this->shared_with_users ?? [];
        $this->update([
            'shared_with_users' => array_values(array_unique(array_merge($current, $userIds)))
        ]);
    }

    public function shareWithTeams(array $teamIds)
    {
        $current = $this->shared_with_teams ?? [];
        $this->update([
            'shared_with_teams' => array_values(array_unique(array_merge($current, $teamIds)))
        ]);
    }

    public function removeUserShare(int $userId)
    {
        $current = $this->shared_with_users ?? [];
        $this->update([
            'shared_with_users' => array_values(array_diff($current, [$userId]))
        ]);
    }

    public function removeTeamShare(int $teamId)
    {
        $current = $this->shared_with_teams ?? [];
        $this->update([
            'shared_with_teams' => array_values(array_diff($current, [$teamId]))
        ]);
    }

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

    public function subscribers()
    {
        return $this->hasMany(ContactGroupSubscriber::class);
    }
}
