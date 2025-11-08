<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * This trait provides team-related functionality to the User model.
 * It handles team ownership, membership, and role management.
 */
trait HasTeams
{
    /**
     * Boot the trait.
     */
    protected static function bootHasTeams()
    {
        static::deleting(function ($user) {
            if (method_exists($user, 'isForceDeleting') && ! $user->isForceDeleting()) {
                return;
            }

            $user->teams()->detach();
            $user->ownedTeams->each->delete();
        });
    }

    /**
     * Get the current team of the user's context.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Get all the teams the user owns or belongs to.
     */
    public function allTeams(): \Illuminate\Support\Collection
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Get all the teams the user owns.
     */
    public function ownedTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get all the teams the user belongs to (excluding owned teams).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot(['role', 'created_at', 'updated_at'])
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * Determine if the user owns the given team.
     */
    public function ownsTeam($team): bool
    {
        if (is_null($team)) {
            return false;
        }

        if (is_numeric($team)) {
            return $this->ownedTeams()->where('id', $team)->exists();
        }

        return $this->id === $team->user_id;
    }

    /**
     * Determine if the user belongs to the given team.
     */
    public function belongsToTeam($team): bool
    {
        if (is_null($team)) {
            return false;
        }

        if ($this->ownsTeam($team)) {
            return true;
        }

        if (is_numeric($team)) {
            return $this->teams()->where('teams.id', $team)->exists();
        }

        return $this->teams->contains('id', $team->id);
    }

    /**
     * Get the role that the user has on the team.
     */
    public function teamRole($team): ?string
    {
        if (is_numeric($team)) {
            $team = $this->teams()->find($team);
        }

        if ($this->ownsTeam($team)) {
            return 'owner';
        }

        return $team ? $team->pivot->role : null;
    }

    /**
     * Determine if the user has the given role on the given team.
     */
    public function hasTeamRole($team, $roles): bool
    {
        if (is_null($team)) {
            return false;
        }

        if ($this->ownsTeam($team)) {
            return true;
        }

        if (is_numeric($team)) {
            $team = $this->teams()->find($team);
        }

        if (! $team) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        $userRole = $team->pivot->role ?? null;

        return in_array($userRole, $roles);
    }

    /**
     * Check if the user has any of the given roles on the team.
     */
    public function hasAnyTeamRole($team, array $roles): bool
    {
        if (is_numeric($team)) {
            $team = Team::find($team);
        }

        if (! $team) {
            return false;
        }

        if ($this->ownsTeam($team)) {
            return true;
        }

        return $this->teams()
            ->where('teams.id', $team->id)
            ->whereIn('role', $roles)
            ->exists();
    }

    /**
     * Add the user to a team with the given role.
     */
    public function addToTeam($team, string $role = 'member'): void
    {
        if ($this->belongsToTeam($team)) {
            return;
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        
        $this->teams()->attach($teamId, ['role' => $role]);
    }

    /**
     * Update the user's role on a team.
     */
    public function updateTeamRole($team, string $role): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        
        return $this->teams()->updateExistingPivot($teamId, [
            'role' => $role,
            'updated_at' => now(),
        ]);
    }

    /**
     * Remove the user from a team.
     */
    public function removeFromTeam($team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        if ($this->ownsTeam($team)) {
            return false; // Prevent removing the owner
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        
        return (bool) $this->teams()->detach($teamId);
    }

    /**
     * Switch the user's context to the given team.
     */
    public function switchTeam($team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        
        $this->forceFill([
            'current_team_id' => $teamId,
        ])->save();

        return true;
    }

    /**
     * Get all of the teams the user can access.
     */
    public function accessibleTeams()
    {
        return Team::where(function ($query) {
            $query->where('user_id', $this->id)
                ->orWhereHas('users', function ($query) {
                    $query->where('user_id', $this->id);
                });
        })->get();
    }

    /**
     * Check if the user is the only owner of a team.
     */
    public function isOnlyOwnerOf($team): bool
    {
        if (is_numeric($team)) {
            $team = Team::find($team);
        }

        if (! $team) {
            return false;
        }

        return $this->ownsTeam($team) && 
               $team->users()->wherePivot('role', 'owner')->count() === 1;
    }

    /**
     * Transfer team ownership to another user.
     */
    public function transferTeamOwnership($team, $newOwner): bool
    {
        if (! $this->ownsTeam($team)) {
            return false;
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        
        // Update team ownership
        Team::where('id', $teamId)->update(['user_id' => $newOwner->id]);
        
        // Update the role in the pivot table
        $this->teams()->updateExistingPivot($teamId, ['role' => 'admin']);
        
        return true;
    }
    
    /**
     * Get the user's role for the current team.
     */
    public function currentTeamRole(): ?string
    {
        if (! $this->current_team_id) {
            return null;
        }
        
        return $this->teamRole($this->current_team_id);
    }
}
