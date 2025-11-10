<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamUser extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_INVITED = 'invited';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BANNED = 'banned';

    protected $table = 'team_user';

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'permissions',
        'metadata',
        'temporal',
        '_status',
        'status_reason',
        'status_changed_at',
        'status_changed_by',
        'added_by',
        'notes',
    ];

    protected $casts = [
        'permissions' => 'array',
        'metadata' => 'array',
        'temporal' => 'array',
        'status_changed_at' => 'datetime',
    ];

    protected $dates = [
        'status_changed_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusChanger()
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeActive($query)
    {
        return $query->where('_status', self::STATUS_ACTIVE);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function isActive(): bool
    {
        return $this->_status === self::STATUS_ACTIVE;
    }

    public function isOwner(): bool
    {
        return $this->role === Team::OWNER;
    }

    public function isMember(): bool
    {
        return $this->role === Team::MEMBER;
    }

    public function getPermissionsAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [
            'can_invite' => false,
            'can_manage_members' => false,
            'can_edit_team' => false,
            'can_delete_team' => false,
            'can_manage_roles' => false,
            'can_manage_settings' => false,
            'can_manage_billing' => false,
            'can_export_data' => false
        ];
    }

    public function getMetadataAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [
            'invitation_token' => null,
            'invited_by' => null,
            'invited_at' => null,
            'joined_at' => null,
            'last_active_at' => null,
            'mfa_enabled' => false,
            'mfa_method' => null,
            'timezone' => 'Africa/Nairobi',
            'preferences' => [
                'notifications' => [
                    'email' => true,
                    'in_app' => true,
                    'push' => true
                ],
                'language' => 'en',
                'theme' => 'system'
            ]
        ];
    }

    public function getTemporalAttribute($value)
    {
        return is_array($value) ? $value : json_decode($value, true) ?? [
            'is_temporary' => false,
            'starts_at' => null,
            'expires_at' => null,
            'time_restrictions' => [
                'enabled' => false,
                'timezone' => 'Africa/Nairobi',
                'schedule' => []
            ]
        ];
    }

    protected static function booted()
    {
        static::saving(function ($teamUser) {
            if (is_array($teamUser->permissions)) {
                $teamUser->permissions = json_encode($teamUser->permissions);
            }
            if (is_array($teamUser->metadata)) {
                $teamUser->metadata = json_encode($teamUser->metadata);
            }
            if (is_array($teamUser->temporal)) {
                $teamUser->temporal = json_encode($teamUser->temporal);
            }
        });
    }
}