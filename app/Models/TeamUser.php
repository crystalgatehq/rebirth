<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamUser extends Model
{
    use HasFactory, SoftDeletes;

    // Role constants
    public const OWNER = 'owner';
    public const MEMBER = 'member';

    // Status constants
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
        'constraints',
        'play',
        'activities',
        'status',
    ];

    protected $casts = [
        'constraints' => 'array',
        'activities' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the team that owns the team user.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user that owns the team user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active team users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if the user is an owner of the team.
     */
    public function isOwner(): bool
    {
        return $this->play === self::OWNER;
    }

    /**
     * Check if the user is a member of the team.
     */
    public function isMember(): bool
    {
        return $this->play === self::MEMBER;
    }

    /**
     * Get the activities attribute with default value.
     */
    public function getActivitiesAttribute($value)
    {
        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }

    /**
     * Get the constraints attribute with default value.
     */
    public function getConstraintsAttribute($value)
    {
        return is_array($value) ? $value : (json_decode($value, true) ?? []);
    }


    protected static function booted()
    {
        static::saving(function ($teamUser) {
            if (is_array($teamUser->activities)) {
                $teamUser->activities = json_encode($teamUser->activities);
            }
            if (is_array($teamUser->constraints)) {
                $teamUser->constraints = json_encode($teamUser->constraints);
            }
        });
    }
}