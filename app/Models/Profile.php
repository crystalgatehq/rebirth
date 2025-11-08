<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    // =====================================================================
    // Constants
    // =====================================================================
    public const STATUS_PENDING   = 0;
    public const STATUS_ACTIVE   = 1;
    public const STATUS_SUSPENDED = 2;

    public const SALUTATION_MR = 0;
    public const SALUTATION_MRS = 1;
    public const SALUTATION_MS = 2;
    public const SALUTATION_MISS = 3;
    public const SALUTATION_DR = 4;
    public const SALUTATION_PROF = 5;
    public const SALUTATION_SIR = 6;
    public const SALUTATION_MADAM = 7;
    public const SALUTATION_MX = 8;

    public const GENDER_FEMALE = 0;
    public const GENDER_MALE = 1;
    public const GENDER_OTHER = 2;
    public const GENDER_PREFER_NOT_TO_SAY = 3;

    public const RACE_WHITE = 0;
    public const RACE_BLACK = 1;
    public const RACE_ASIAN = 2;
    public const RACE_HISPANIC = 3;
    public const RACE_OTHER = 4;
    public const RACE_PREFER_NOT_TO_SAY = 5;

    public const ETHNICITY_AFRICAN = 0;
    public const ETHNICITY_ASIAN = 1;
    public const ETHNICITY_EUROPEAN = 2;
    public const ETHNICITY_HISPANIC = 3;
    public const ETHNICITY_NORTH_ASIAN = 4;
    public const ETHNICITY_SOUTH_ASIAN = 5;
    public const ETHNICITY_AUSTRALIAN = 6;
    public const ETHNICITY_HAWAIIAN = 7;
    public const ETHNICITY_MIDDLE_EASTERN = 8;
    public const ETHNICITY_NATIVE_AMERICAN = 9;
    public const ETHNICITY_SOUTH_AMERICAN = 10;
    public const ETHNICITY_PACIFIC_ISLANDER = 11;
    public const ETHNICITY_CAUCASIAN = 12;
    public const ETHNICITY_OTHER = 13;

    public const RELIGION_CHRISTIAN = 0;
    public const RELIGION_MUSLIM = 1;
    public const RELIGION_HINDU = 2;
    public const RELIGION_BUDHIST = 3;
    public const RELIGION_JEWISH = 4;
    public const RELIGION_SIKH = 5;
    public const RELIGION_MORMON = 6;
    public const RELIGION_OTHER = 7;

    public const MARITAL_STATUS_MARRIED   = 0;
    public const MARITAL_STATUS_SINGLE   = 1;
    public const MARITAL_STATUS_DIVORCED = 2;

    // =====================================================================
    // Table & Fillable
    // =====================================================================
    protected $table = 'profiles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'salutation',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'marital_status',
        'date_of_birth',
        'biography',
        'social_links',
        'telephone',
        'address_line_1',
        'address_line_2',
        'city',
        '_state',
        'country',
        '_timezone',
        '_locale',
        'tax_number',
        'national_id_number',
        'passport_number',
        'drivers_license_number',
        'vehicle_registration_number',
        'configuration',
        '_status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'social_links' => 'array',
        'configuration' => 'array',
        '_status' => 'integer',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', [
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]));
    }

    /**
     * Get the user's address as a single string.
     */
    public function getFullAddressAttribute(): ?string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->_state,
            $this->country
        ];

        $filtered = array_filter($parts);
        return $filtered ? implode(', ', $filtered) : null;
    }
}
