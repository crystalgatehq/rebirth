<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Biometric extends Model
{
    use HasFactory, SoftDeletes;

    // =====================================================================
    // Constants
    // =====================================================================

    // Verification Status Constants
    public const VERIFICATION_UNVERIFIED = 0;
    public const VERIFICATION_PENDING = 1;
    public const VERIFICATION_VERIFIED = 2;
    public const VERIFICATION_REJECTED = 3;

    // Blood Type Constants
    public const BLOOD_TYPE_A_POSITIVE = 0;
    public const BLOOD_TYPE_A_NEGATIVE = 1;
    public const BLOOD_TYPE_B_POSITIVE = 2;
    public const BLOOD_TYPE_B_NEGATIVE = 3;
    public const BLOOD_TYPE_AB_POSITIVE = 4;
    public const BLOOD_TYPE_AB_NEGATIVE = 5;
    public const BLOOD_TYPE_O_POSITIVE = 6;
    public const BLOOD_TYPE_O_NEGATIVE = 7;

    // Body Build Type Constants
    public const BODY_BUILD_TYPE_SLIM = 0;
    public const BODY_BUILD_TYPE_ATHLETIC = 1;
    public const BODY_BUILD_TYPE_AVERAGE = 2;
    public const BODY_BUILD_TYPE_MUSCULAR = 3;
    public const BODY_BUILD_TYPE_OVERWEIGHT = 4;
    public const BODY_BUILD_TYPE_OBESE = 5;

    // Distinguishing Features Constants
    public const DISTINGUISHING_FEATURES_TATTOOS = 0;
    public const DISTINGUISHING_FEATURES_SCARS = 1;
    public const DISTINGUISHING_FEATURES_BIRTHMARKS = 2;
    public const DISTINGUISHING_FEATURES_OTHER = 3;

    // Eye Color Constants
    public const EYE_COLOR_BROWN = 0;
    public const EYE_COLOR_BLUE = 1;
    public const EYE_COLOR_GREEN = 2;
    public const EYE_COLOR_HAZEL = 3;
    public const EYE_COLOR_VIOLET = 4;
    public const EYE_COLOR_GREY = 5;

    // Eye Shape Constants
    public const EYE_SHAPE_ROUND = 0;
    public const EYE_SHAPE_ELLIPSE = 1;
    public const EYE_SHAPE_OVAL = 2;
    public const EYE_SHAPE_SQUARE = 3;
    public const EYE_SHAPE_TRIANGULAR = 4;

    // Hair Color Constants
    public const HAIR_COLOR_BROWN = 0;
    public const HAIR_COLOR_BLONDE = 1;
    public const HAIR_COLOR_RED = 2;
    public const HAIR_COLOR_BLACK = 3;
    public const HAIR_COLOR_WHITE = 4;

    // Skin Tone Constants
    public const SKIN_TONE_LIGHT = 0;
    public const SKIN_TONE_MEDIUM = 1;
    public const SKIN_TONE_DARK = 2;

    // Fingerprint Data Constants
    public const FINGERPRINT_DATA_LEFT_THUMB = 0;
    public const FINGERPRINT_DATA_RIGHT_THUMB = 1;
    public const FINGERPRINT_DATA_LEFT_INDEX = 2;
    public const FINGERPRINT_DATA_RIGHT_INDEX = 3;
    public const FINGERPRINT_DATA_LEFT_MIDDLE = 4;
    public const FINGERPRINT_DATA_RIGHT_MIDDLE = 5;
    public const FINGERPRINT_DATA_LEFT_RING = 6;
    public const FINGERPRINT_DATA_RIGHT_RING = 7;
    public const FINGERPRINT_DATA_LEFT_LITTLE = 8;
    public const FINGERPRINT_DATA_RIGHT_LITTLE = 9;

    // Facial Recognition Data Constants
    public const FACIAL_RECOGNITION_DATA_LEFT_EYE = 0;
    public const FACIAL_RECOGNITION_DATA_RIGHT_EYE = 1;
    public const FACIAL_RECOGNITION_DATA_NOSE = 2;
    public const FACIAL_RECOGNITION_DATA_MOUTH = 3;
    public const FACIAL_RECOGNITION_DATA_LEFT_EAR = 4;
    public const FACIAL_RECOGNITION_DATA_RIGHT_EAR = 5;
    public const FACIAL_RECOGNITION_DATA_LEFT_CHEEK = 6;
    public const FACIAL_RECOGNITION_DATA_RIGHT_CHEEK = 7;
    public const FACIAL_RECOGNITION_DATA_LEFT_JAW = 8;
    public const FACIAL_RECOGNITION_DATA_RIGHT_JAW = 9;

    // Retina Scan Data Constants
    public const RETINA_SCAN_DATA_LEFT_EYE = 0;
    public const RETINA_SCAN_DATA_RIGHT_EYE = 1;

    // Voice Recognition Data Constants
    public const VOICE_RECOGNITION_DATA_LEFT_VOICE = 0;
    public const VOICE_RECOGNITION_DATA_RIGHT_VOICE = 1;

    // Medical History Constants
    public const MEDICAL_HISTORY_NONE = 0;
    public const MEDICAL_HISTORY_DIABETES = 1;
    public const MEDICAL_HISTORY_HYPERTENSION = 2;
    public const MEDICAL_HISTORY_HEART_DISEASE = 3;
    public const MEDICAL_HISTORY_CANCER = 4;
    public const MEDICAL_HISTORY_STROKE = 5;
    public const MEDICAL_HISTORY_ASTHMA = 6;
    public const MEDICAL_HISTORY_ARTHRITIS = 7;
    public const MEDICAL_HISTORY_EPILEPSY = 8;
    public const MEDICAL_HISTORY_ANXIETY = 9;
    public const MEDICAL_HISTORY_DEPRESSION = 10;
    public const MEDICAL_HISTORY_OTHER = 99;

    // Condition Constants
    public const CONDITION_ACUTE = 0;
    public const CONDITION_CHRONIC = 1;
    public const CONDITION_GENETIC = 2;
    public const CONDITION_AUTO_IMMUNE = 3;
    public const CONDITION_INFECTIOUS = 4;
    public const CONDITION_METABOLIC = 5;
    public const CONDITION_NEUROLOGICAL = 6;
    public const CONDITION_PSYCHIATRIC = 7;
    public const CONDITION_RESPIRATORY = 8;
    public const CONDITION_CARDIOVASCULAR = 9;

    // Treatment Type Constants
    public const TREATMENT_MEDICATION = 0;
    public const TREATMENT_THERAPY = 1;
    public const TREATMENT_SURGERY = 2;
    public const TREATMENT_RADIATION = 3;
    public const TREATMENT_CHEMOTHERAPY = 4;
    public const TREATMENT_PHYSIOTHERAPY = 5;
    public const TREATMENT_PSYCHOTHERAPY = 6;
    public const TREATMENT_LIFESTYLE = 7;

    // Test Type Constants
    public const TEST_BLOOD = 0;
    public const TEST_URINE = 1;
    public const TEST_IMAGING = 2;
    public const TEST_GENETIC = 3;
    public const TEST_BIOMARKER = 4;
    public const TEST_NEUROLOGICAL = 5;
    public const TEST_CARDIO = 6;
    public const TEST_RESPIRATORY = 7;

    // Vaccination Type Constants
    public const VACCINE_INFLUENZA = 0;
    public const VACCINE_HEPATITIS_B = 1;
    public const VACCINE_MEASLES = 2;
    public const VACCINE_HPV = 3;
    public const VACCINE_COVID_19 = 4;
    public const VACCINE_TETANUS = 5;
    public const VACCINE_PNEUMOCOCCAL = 6;
    public const VACCINE_SHINGLES = 7;

    // Medication Type Constants
    public const MEDICATION_ANALGESIC = 0;
    public const MEDICATION_ANTIBIOTIC = 1;
    public const MEDICATION_ANTIDEPRESSANT = 2;
    public const MEDICATION_ANTIHISTAMINE = 3;
    public const MEDICATION_ANTIHYPERTENSIVE = 4;
    public const MEDICATION_ANTI_INFLAMMATORY = 5;
    public const MEDICATION_DIABETES = 6;
    public const MEDICATION_STATIN = 7;

    // Allergies Constants
    public const ALLERGENS_POLLEN = 0;
    public const ALLERGENS_PEANUTS = 1;
    public const ALLERGENS_SHELLFISH = 2;
    public const ALLERGENS_LATEX = 3;
    public const ALLERGENS_PENICILLIN = 4;
    public const ALLERGENS_DUST = 5;
    public const ALLERGENS_MOLD = 6;
    public const ALLERGENS_ANIMAL_DANDER = 7;
    public const ALLERGENS_BEE_STINGS = 8;
    public const ALLERGENS_SUNLIGHT = 9;
    public const ALLERGENS_MEDICATIONS = 10;
    public const ALLERGENS_FOOD_ADDITIVES = 11;
    public const ALLERGENS_ENVIRONMENTAL_POLLUTANTS = 12;
    public const ALLERGENS_OTHER = 13;
    
    // Procedure Type Constants
    public const PROCEDURE_DIAGNOSTIC = 0;
    public const PROCEDURE_THERAPEUTIC = 1;
    public const PROCEDURE_COSMETIC = 2;
    public const PROCEDURE_EMERGENCY = 3;
    public const PROCEDURE_PREVENTIVE = 4;
    public const PROCEDURE_REHABILITATIVE = 5;

    // Severity Level Constants
    public const SEVERITY_MILD = 0;
    public const SEVERITY_MODERATE = 1;
    public const SEVERITY_SEVERE = 2;
    public const SEVERITY_CRITICAL = 3;

    // Status Constants
    public const STATUS_PENDING = 0;
    public const STATUS_PROCESSING = 1;
    public const STATUS_VERIFIED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_FAILED = 4;
    public const STATUS_CANCELLED = 5;
    public const STATUS_EXPIRED = 6;
    public const STATUS_ARCHIVED = 7;

    // =====================================================================
    // Methods
    // =====================================================================

    // Get blood type options
    public static function getBloodTypeOptions()
    {
        return [
            self::BLOOD_TYPE_A_POSITIVE => 'A+',
            self::BLOOD_TYPE_A_NEGATIVE => 'A-',
            self::BLOOD_TYPE_B_POSITIVE => 'B+',
            self::BLOOD_TYPE_B_NEGATIVE => 'B-',
            self::BLOOD_TYPE_AB_POSITIVE => 'AB+',
            self::BLOOD_TYPE_AB_NEGATIVE => 'AB-',
            self::BLOOD_TYPE_O_POSITIVE => 'O+',
            self::BLOOD_TYPE_O_NEGATIVE => 'O-',
        ];
    }

    // Get blood type value by label
    public static function getBloodTypeValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getBloodTypeOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get body build type options
    public static function getBodyBuildTypeOptions()
    {
        return [
            self::BODY_BUILD_TYPE_SLIM => 'Slim',
            self::BODY_BUILD_TYPE_ATHLETIC => 'Athletic',
            self::BODY_BUILD_TYPE_AVERAGE => 'Average',
            self::BODY_BUILD_TYPE_MUSCULAR => 'Muscular',
            self::BODY_BUILD_TYPE_OVERWEIGHT => 'Overweight',
            self::BODY_BUILD_TYPE_OBESE => 'Obese',
        ];
    }

    // Get body build type value by label
    public static function getBodyBuildTypeValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getBodyBuildTypeOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get eye color options
    public static function getEyeColorOptions()
    {
        return [
            self::EYE_COLOR_BROWN => 'Brown',
            self::EYE_COLOR_BLUE => 'Blue',
            self::EYE_COLOR_GREEN => 'Green',
            self::EYE_COLOR_HAZEL => 'Hazel',
            self::EYE_COLOR_VIOLET => 'Violet',
            self::EYE_COLOR_GREY => 'Grey'
        ];
    }

    // Get eye color value by label
    public static function getEyeColorValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getEyeColorOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get eye shape options
    public static function getEyeShapeOptions()
    {
        return [
            self::EYE_SHAPE_ROUND => 'Round',
            self::EYE_SHAPE_ELLIPSE => 'Ellipse',
            self::EYE_SHAPE_OVAL => 'Oval',
            self::EYE_SHAPE_SQUARE => 'Square',
            self::EYE_SHAPE_TRIANGULAR => 'Triangular',
        ];
    }

    // Get eye shape value by label
    public static function getEyeShapeValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getEyeShapeOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get hair color options
    public static function getHairColorOptions()
    {
        return [
            self::HAIR_COLOR_BROWN => 'Brown',
            self::HAIR_COLOR_BLONDE => 'Blonde',
            self::HAIR_COLOR_RED => 'Red',
            self::HAIR_COLOR_BLACK => 'Black',
            self::HAIR_COLOR_WHITE => 'White',
        ];
    }

    // Get hair color value by label
    public static function getHairColorValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getHairColorOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get skin tone options
    public static function getSkinToneOptions()
    {
        return [
            self::SKIN_TONE_LIGHT => 'Light',
            self::SKIN_TONE_MEDIUM => 'Medium',
            self::SKIN_TONE_DARK => 'Dark',
        ];
    }

    // Get skin tone value by label
    public static function getSkinToneValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getSkinToneOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get fingerprint options
    public static function getFingerprintOptions()
    {
        return [
            self::FINGERPRINT_DATA_LEFT_THUMB => 'Left thumb',
            self::FINGERPRINT_DATA_RIGHT_THUMB => 'Right thumb',
            self::FINGERPRINT_DATA_LEFT_INDEX => 'Left index finger',
            self::FINGERPRINT_DATA_RIGHT_INDEX => 'Right index finger',
            self::FINGERPRINT_DATA_LEFT_MIDDLE => 'Left middle finger',
            self::FINGERPRINT_DATA_RIGHT_MIDDLE => 'Right middle finger',
            self::FINGERPRINT_DATA_LEFT_RING => 'Left ring finger',
            self::FINGERPRINT_DATA_RIGHT_RING => 'Right ring finger',
            self::FINGERPRINT_DATA_LEFT_LITTLE => 'Left little finger',
            self::FINGERPRINT_DATA_RIGHT_LITTLE => 'Right little finger',
        ];
    }

    // Get fingerprint value by label
    public static function getFingerprintValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getFingerprintOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get facial recognition options
    public static function getFacialRecognitionOptions()
    {
        return [
            self::FACIAL_RECOGNITION_DATA_LEFT_EYE => 'Left eye',
            self::FACIAL_RECOGNITION_DATA_RIGHT_EYE => 'Right eye',
            self::FACIAL_RECOGNITION_DATA_NOSE => 'Nose',
            self::FACIAL_RECOGNITION_DATA_MOUTH => 'Mouth',
            self::FACIAL_RECOGNITION_DATA_LEFT_EAR => 'Left ear',
            self::FACIAL_RECOGNITION_DATA_RIGHT_EAR => 'Right ear',
            self::FACIAL_RECOGNITION_DATA_LEFT_CHEEK => 'Left cheek',
            self::FACIAL_RECOGNITION_DATA_RIGHT_CHEEK => 'Right cheek',
            self::FACIAL_RECOGNITION_DATA_LEFT_JAW => 'Left jaw',
            self::FACIAL_RECOGNITION_DATA_RIGHT_JAW => 'Right jaw',
        ];
    }

    // Get facial recognition value by label
    public static function getFacialRecognitionValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getFacialRecognitionOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get retina scan options
    public static function getRetinaScanOptions()
    {
        return [
            self::RETINA_SCAN_DATA_LEFT_EYE => 'Left eye',
            self::RETINA_SCAN_DATA_RIGHT_EYE => 'Right eye',
        ];
    }

    // Get retina scan value by label
    public static function getRetinaScanValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getRetinaScanOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get voice recognition options
    public static function getVoiceRecognitionOptions()
    {
        return [
            self::VOICE_RECOGNITION_DATA_LEFT_VOICE => 'Left voice',
            self::VOICE_RECOGNITION_DATA_RIGHT_VOICE => 'Right voice',
        ];
    }

    // Get voice recognition value by label
    public static function getVoiceRecognitionValueByLabel($label)
    {
        // Perform case-insensitive search
        $lowerLabel = strtolower(explodeUppercase($label));

        foreach (self::getVoiceRecognitionOptions() as $key => $value) {
            if (strpos(strtolower($value), $lowerLabel) !== false) {
                return $key;
            }
        }
        
        return false;
    }

    // Get configuration options

    // =====================================================================
    // Table & Fillable
    // =====================================================================

    protected $table = 'biometrics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'blood_type',
        'height_cm',
        'weight_kg',
        'body_build_type',
        'eye_color',
        'eye_shape',
        'hair_color',
        'skin_tone',
        'allergies',
        'distinguishing_features',
        'fingerprint_data',
        'facial_recognition_data',
        'retina_scan_data',
        'last_updated_at',
        '_status',
        'metadata',
        'medical_records',
        'configuration',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'height_cm' => 'integer',
        'weight_kg' => 'float',
        'allergies' => 'array',
        'distinguishing_features' => 'array',
        'fingerprint_data' => 'array',
        'facial_recognition_data' => 'array',
        'retina_scan_data' => 'array',
        'metadata' => 'array',
        'medical_records' => 'array',
        'configuration' => 'array',
        'last_updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * Get the user that owns the biometric record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =====================================================================
    // Accessors & Mutators
    // =====================================================================

    /**
     * Get the blood type as a human-readable string.
     */
    public function getBloodTypeNameAttribute(): ?string
    {
        return match($this->blood_type) {
            self::BLOOD_TYPE_A_POSITIVE => 'A+',
            self::BLOOD_TYPE_A_NEGATIVE => 'A-',
            self::BLOOD_TYPE_B_POSITIVE => 'B+',
            self::BLOOD_TYPE_B_NEGATIVE => 'B-',
            self::BLOOD_TYPE_AB_POSITIVE => 'AB+',
            self::BLOOD_TYPE_AB_NEGATIVE => 'AB-',
            self::BLOOD_TYPE_O_POSITIVE => 'O+',
            self::BLOOD_TYPE_O_NEGATIVE => 'O-',
            default => null,
        };
    }

    /**
     * Get all blood types as an associative array.
     */
    public static function getBloodTypes(): array
    {
        return [
            self::BLOOD_TYPE_A_POSITIVE => 'A+',
            self::BLOOD_TYPE_A_NEGATIVE => 'A-',
            self::BLOOD_TYPE_B_POSITIVE => 'B+',
            self::BLOOD_TYPE_B_NEGATIVE => 'B-',
            self::BLOOD_TYPE_AB_POSITIVE => 'AB+',
            self::BLOOD_TYPE_AB_NEGATIVE => 'AB-',
            self::BLOOD_TYPE_O_POSITIVE => 'O+',
            self::BLOOD_TYPE_O_NEGATIVE => 'O-',
        ];
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Scope a query to only include verified biometric records.
     */
    public function scopeVerified($query)
    {
        return $query->where('_status', self::STATUS_VERIFIED);
    }

    /**
     * Scope a query to only include active biometric records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // =====================================================================
    // Helper Methods
    // =====================================================================

    /**
     * Check if the biometric record is verified.
     */
    public function isVerified(): bool
    {
        return $this->_status === self::STATUS_VERIFIED;
    }

    /**
     * Check if the biometric record is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Mark the biometric record as verified.
     */
    public function markAsVerified(): bool
    {
        return $this->update([
            '_status' => self::STATUS_VERIFIED,
            'last_updated_at' => now(),
        ]);
    }
}
