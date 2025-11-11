<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE   = 1;
    public const STATUS_PENDING  = 2;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        '_hierarchy_matrix_level',
        'status',
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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'string',
        '_hierarchy_matrix_level' => 'integer',
        'status' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Check if the role is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get the hierarchy level of a role by its slug.
     *
     * @param string $roleSlug The role's slug
     * @return int The hierarchy level (0 if role not found)
     */
    public static function getHierarchyLevel(string $roleSlug): int
    {
        return (int) static::where('slug', $roleSlug)
            ->value('_hierarchy_matrix_level');
    }

    /**
     * Check if a user can assign a specific role
     */
    public static function canAssignRole(string $assignerRole, string $targetRole): bool
    {
        $assignerLevel = self::getHierarchyLevel($assignerRole);
        $targetLevel = self::getHierarchyLevel($targetRole);
        
        // Can only assign roles that are below or equal to their own level
        // but not higher than their own level
        return $assignerLevel > 0 && $targetLevel > 0 && $targetLevel <= $assignerLevel;
    }

    /**
     * Get all roles that a user with a given role can assign.
     *
     * @param string $userRoleSlug The slug of the user's role
     * @return array<string, string> Array of role names keyed by slug
     */
    public static function getAssignableRoles(string $userRoleSlug): array
    {
        $userLevel = static::getHierarchyLevel($userRoleSlug);
        
        if ($userLevel <= 0) {
            return [];
        }
        
        return static::query()
            ->where('_hierarchy_matrix_level', '>', 0)
            ->where('_hierarchy_matrix_level', '<=', $userLevel)
            ->orderBy('_hierarchy_matrix_level', 'desc')
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * Users assigned to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps();
    }

    /**
     * Abilities (permissions) assigned to this role.
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'ability_role')
                    ->withTimestamps();
    }

    /**
     * Scope: Active roles only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if role has a specific ability by name or slug.
     */
    public function hasAbility(string $ability): bool
    {
        return $this->abilities()
            ->where(function ($q) use ($ability) {
                $q->where('name', $ability)
                  ->orWhere('slug', $ability);
            })
            ->exists();
    }

    /**
     * Activate the role.
     */
    public function activate(): self
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
        return $this;
    }

    /**
     * Deactivate the role.
     */
    public function deactivate(): self
    {
        $this->update(['status' => self::STATUS_INACTIVE]);
        return $this;
    }
}