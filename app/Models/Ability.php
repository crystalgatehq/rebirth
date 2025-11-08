<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ability extends Model
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
    protected $table = 'abilities';

    protected $fillable = [
        'name',
        '_slug',
        'description',
        '_status',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        '_status'    => 'integer',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * Roles that have this ability.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'ability_role')
                    ->withTimestamps();
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Scope: Active abilities only.
     */
    public function scopeActive($query)
    {
        return $query->where('_status', self::ACTIVE);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * Activate the ability.
     */
    public function activate(): self
    {
        $this->update(['_status' => self::ACTIVE]);
        return $this;
    }

    /**
     * Deactivate the ability.
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