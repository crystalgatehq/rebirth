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

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'team_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'user_id',
        'constraints',
        'play',
        'activities',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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
}