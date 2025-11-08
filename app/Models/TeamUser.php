<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TeamUser extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}
