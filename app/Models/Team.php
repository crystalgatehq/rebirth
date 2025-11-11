<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_SUSPENDED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'owner_id',
        'name',
        'display_name',
        'abbrivated_name',
        'slug',
        'description',
        'personal_team',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'string',
        'owner_id' => 'integer',
        'personal_team' => 'boolean',
        'status' => 'integer'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the owner of the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all users that are members of the team (excluding the owner).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withTimestamps()
            ->withPivot(['status', 'added_by', 'status_changed_at']);
    }

    /**
     * Check if a user is associated with this team (either as owner or member).
     *
     * @param  User|int  $user
     * @return bool
     */
    public function hasUser($user): bool
    {
        if ($user instanceof User) {
            return $this->isOwnedBy($user) || $this->users->contains('id', $user->id);
        }
        
        return $this->owner_id === $user || $this->users->contains('id', $user);
    }

    /**
     * Check if the given user is the owner of the team.
     *
     * @param  User|int  $user
     * @return bool
     */
    public function isOwnedBy($user): bool
    {
        if ($user instanceof User) {
            return $this->owner_id === $user->id;
        }
        
        return $this->owner_id === $user;
    }

    /**
     * Add a user to the team.
     *
     * @param  User  $user
     * @param  int|null  $addedBy
     * @return bool
     */
    public function addUser(User $user, ?int $addedBy = null): bool
    {
        if ($this->isOwnedBy($user)) {
            return false; // User is already the owner
        }

        if ($this->users->contains('id', $user->id)) {
            return false; // User is already a member
        }

        $this->users()->attach($user->id, [
            'added_by' => $addedBy,
            'status' => self::STATUS_ACTIVE,
            'status_changed_at' => now(),
            'status_changed_by' => $addedBy,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return true;
    }

    /**
     * Remove a user from the team.
     *
     * @param  User  $user
     * @return bool
     */
    public function removeUser(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return false; // Cannot remove the owner this way
        }

        return (bool) $this->users()->detach($user->id);
    }

    /**
     * Check if a user is a member of the team (not the owner).
     *
     * @param  User|int  $user
     * @return bool
     */
    public function hasMember($user): bool
    {
        if ($user instanceof User) {
            $userId = $user->id;
        } else {
            $userId = $user;
        }

        return $this->users()
            ->where('user_id', $userId)
            ->where('status', self::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Check if the given user is the owner of the team.
     *
     * @param  User|int  $user
     * @return bool
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Check if the given user is a member of the team (not the owner).
     *
     * @param  User|int  $user
     * @return bool
     */
    public function isMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('status', self::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Scope a query to only include active teams.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include verified teams.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true)
            ->whereNotNull('verified_at');
    }

    /**
     * Scope a query to only include teams of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include teams with active members.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithActiveMembers($query)
    {
        return $query->whereHas('members', function ($q) {
            $q->where('status', self::STATUS_ACTIVE);
        });
    }
}