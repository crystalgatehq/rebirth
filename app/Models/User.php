<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasTwoFactorAuthentication;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasTeams;
use App\Models\Profile;
use App\Models\Team;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasTwoFactorAuthentication, HasTeams;

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
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
        'profile_photo_path',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at' => 'datetime'
    ];

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * The teams that the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot(['role', 'created_at', 'updated_at'])
            ->withTimestamps();
    }

    /**
     * Get the user's biometric data.
     */
    public function biometric(): HasOne
    {
        return $this->hasOne(Biometric::class);
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps()
            ->withPivot('created_at', 'updated_at');
    }

    /**
     * The teams that the user owns.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the user's current team.
     */
    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Get the URL to the user's profile photo.
     */
    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo_path
            ? asset('storage/'.$this->profile_photo_path)
            : $this->defaultProfilePhotoUrl();
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     */
    protected function defaultProfilePhotoUrl(): string
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param  array|string  $roles
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if the user has all of the given roles.
     *
     * @param  array  $roles
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Check if the user has a specific ability.
     */
    public function hasAbility(string $ability): bool
    {
        return $this->roles()->whereHas('abilities', function ($query) use ($ability) {
            $query->where('name', $ability);
        })->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('_slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('_slug', $role)->firstOrFail();
        }

        $this->roles()->detach($role->id);
    }

    /**
     * Determine if the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    /**
     * Determine if the user is a super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Determine if the user owns the given team.
     */
    public function ownsTeam($team): bool
    {
        if (is_null($team)) {
            return false;
        }

        return $this->id == $team->user_id;
    }

    /**
     * Get the user's preferred locale.
     */
    public function preferredLocale()
    {
        return $this->profile?->locale ?? config('app.locale');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('_status', 1);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('_status', 0);
    }

    /**
     * Get the user's status as a string.
     */
    public function getStatusAttribute(): string
    {
        return $this->_status === 1 ? 'active' : 'inactive';
    }

    /**
     * Update the user's last login timestamp and IP.
     */
    public function recordLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }

    /**
     * Check if the user is the owner of the given team.
     */
    public function isTeamOwner(Team $team): bool
    {
        return $this->id === $team->user_id;
    }

    /**
     * Check if the user is a member of the given team.
     */
    public function isMemberOf(Team $team): bool
    {
        return $this->teams->contains($team->id);
    }

    /**
     * Get the user's team role for a specific team.
     */
    public function teamRole(Team $team): ?string
    {
        return $this->teams()->where('team_id', $team->id)->first()?->pivot->role;
    }
}
