<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;

class Team extends Model
{
    use HasFactory;

    /**
     * Team status constants
     */
    public const PENDING = 0;
    public const ACTIVE = 1;
    public const SUSPENDED = 2;
    public const ARCHIVED = 3;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        '_slug',
        'description',
        'personal_team',
        '_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'personal_team' => 'boolean',
        '_status' => 'integer',
    ];

    /**
     * Get the owner of the team.
     */
    /**
     * Get the owner of the team.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all the users that belong to the team.
     */
    /**
     * Get all the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot(['role', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->using(TeamUser::class);
    }

    /**
     * Determine if the given user belongs to the team.
     */
    /**
     * Determine if the given user belongs to the team.
     */
    public function hasUser(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the user's role on the team.
     */
    public function getUserRole(User $user): ?string
    {
        $teamUser = $this->users()->where('user_id', $user->id)->first();
        
        return $teamUser ? $teamUser->pivot->role : null;
    }

    /**
     * Add a user to the team with a specific role, with permission checks
     * 
     * @param User $user The user adding the member
     * @param User $member The user to add
     * @param string $role The role to assign
     * @return bool True if successful
     */
    public function addMember(User $user, User $member, string $role): bool
    {
        // Add or update the user's role in the team
        $this->users()->syncWithoutDetaching([
            $member->id => ['role' => $role]
        ]);
        
        return true;
    }
    
    /**
     * Get all assignable roles for the current user in this team
     * 
     * @param User $user The user whose assignable roles to get
     * @return array Array of roles with slug as key and name as value
     */
    public function getAssignableRoles(User $user): array
    {
        $userRole = $this->getUserRole($user);
        
        if (!$userRole) {
            return [];
        }
        
        // Get the role model to ensure we have hierarchy_level
        $role = Role::where('_slug', $userRole)->first();
        
        if (!$role) {
            return [];
        }
        
        return Role::getAssignableRoles($userRole);
    }

    /**
     * Get the team's status as a string.
     */
    public function getStatusAttribute(): string
    {
        return $this->_status === 1 ? 'active' : 'inactive';
    }

    /**
     * Scope a query to only include active teams.
     */
    public function scopeActive($query)
    {
        return $query->where('_status', 1);
    }

    /**
     * Scope a query to only include inactive teams.
     */
    public function scopeInactive($query)
    {
        return $query->where('_status', 0);
    }
}
