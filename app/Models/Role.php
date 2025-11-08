<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    // =====================================================================
    // Constants
    // =====================================================================
    public const PENDING   = 0;
    public const ACTIVE   = 1;
    public const SUSPENDED = 2;

    // =====================================================================
    // Table & Fillable
    // =====================================================================
    protected $table = 'roles';

    protected $fillable = [
        'name',
        '_slug',
        'description',
        '_hierarchy_matrix_level',
        '_status'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        '_hierarchy_matrix_level' => 'integer',
        '_status'    => 'integer'
    ];

    /**
     * Get the hierarchy level of a role
     *
     * @param string $roleSlug The role's slug
     * @return int The hierarchy level (0 if role not found)
     */
    public static function getHierarchyLevel(string $roleSlug): int
    {
        return static::where('_slug', $roleSlug)
            ->value('_hierarchy_matrix_level') ?? 0;
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
     * Get all roles that a user with a given role can assign
     * 
     * @param string $userRoleSlug The slug of the user's role
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getAssignableRoles(string $userRoleSlug)
    {
        $userLevel = static::getHierarchyLevel($userRoleSlug);
        
        return static::where('_hierarchy_matrix_level', '>', 0)
            ->where('_hierarchy_matrix_level', '<=', $userLevel)
            ->orderBy('_hierarchy_matrix_level', 'desc')
            ->pluck('name', '_slug')
            ->toArray();
    }

    // =====================================================================
    // Relationships
    // =====================================================================

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

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Scope: Active roles only.
     */
    public function scopeActive($query)
    {
        return $query->where('_status', self::ACTIVE);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * Check if role has a specific ability by name or _slug.
     */
    public function hasAbility(string $ability): bool
    {
        return $this->abilities()
            ->where(function ($q) use ($ability) {
                $q->where('name', $ability)
                  ->orWhere('_slug', $ability);
            })
            ->exists();
    }

    /**
     * Activate the role.
     */
    public function activate(): self
    {
        $this->update(['_status' => self::ACTIVE]);
        return $this;
    }

    /**
     * Deactivate the role.
     */
    public function deactivate(): self
    {
        $this->update(['_status' => self::SUSPENDED]);
        return $this;
    }

    /**
     * Accessor: Is active?
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->_status === self::ACTIVE;
    }
}