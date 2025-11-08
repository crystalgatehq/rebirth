<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Biometric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BiometricFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Biometric::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            '_uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'blood_type' => $this->getRandomBloodType(),
            'height_cm' => $this->faker->numberBetween(150, 200),
            'weight_kg' => $this->faker->randomFloat(1, 45, 120),
            'body_build_type' => $this->getRandomBodyBuildType(),
            'distinguishing_features' => $this->getRandomDistinguishingFeatures(),
            'eye_color' => $this->faker->randomElement([
                Biometric::EYE_COLOR_BROWN,
                Biometric::EYE_COLOR_BLUE,
                Biometric::EYE_COLOR_GREEN,
                Biometric::EYE_COLOR_HAZEL,
                Biometric::EYE_COLOR_VIOLET,
                Biometric::EYE_COLOR_GREY,
            ]),
            'eye_shape' => $this->faker->randomElement([
                Biometric::EYE_SHAPE_ROUND,
                Biometric::EYE_SHAPE_ELLIPSE,
                Biometric::EYE_SHAPE_OVAL,
                Biometric::EYE_SHAPE_SQUARE,
                Biometric::EYE_SHAPE_TRIANGULAR,
            ]),
            'hair_color' => $this->faker->randomElement([
                Biometric::HAIR_COLOR_BROWN,
                Biometric::HAIR_COLOR_BLONDE,
                Biometric::HAIR_COLOR_RED,
                Biometric::HAIR_COLOR_BLACK,
                Biometric::HAIR_COLOR_WHITE,
            ]),
            'skin_tone' => $this->faker->randomElement([
                Biometric::SKIN_TONE_LIGHT,
                Biometric::SKIN_TONE_MEDIUM,
                Biometric::SKIN_TONE_DARK,
            ]),
            'fingerprint_data' => json_encode([
                'type' => $this->faker->randomElement([
                    Biometric::FINGERPRINT_DATA_LEFT_THUMB,
                    Biometric::FINGERPRINT_DATA_RIGHT_THUMB,
                    Biometric::FINGERPRINT_DATA_LEFT_INDEX,
                    Biometric::FINGERPRINT_DATA_RIGHT_INDEX,
                    Biometric::FINGERPRINT_DATA_LEFT_MIDDLE,
                    Biometric::FINGERPRINT_DATA_RIGHT_MIDDLE,
                    Biometric::FINGERPRINT_DATA_LEFT_RING,
                    Biometric::FINGERPRINT_DATA_RIGHT_RING,
                    Biometric::FINGERPRINT_DATA_LEFT_LITTLE,
                    Biometric::FINGERPRINT_DATA_RIGHT_LITTLE,
                ]),
                'data' => $this->faker->sha256(),
                'created_at' => now()->toDateTimeString(),
            ]),
            'facial_recognition_data' => json_encode([
                'type' => $this->faker->randomElement([
                    Biometric::FACIAL_RECOGNITION_DATA_LEFT_EYE,
                    Biometric::FACIAL_RECOGNITION_DATA_RIGHT_EYE,
                    Biometric::FACIAL_RECOGNITION_DATA_NOSE,
                    Biometric::FACIAL_RECOGNITION_DATA_MOUTH,
                    Biometric::FACIAL_RECOGNITION_DATA_LEFT_EAR,
                    Biometric::FACIAL_RECOGNITION_DATA_RIGHT_EAR,
                    Biometric::FACIAL_RECOGNITION_DATA_LEFT_CHEEK,
                    Biometric::FACIAL_RECOGNITION_DATA_RIGHT_CHEEK,
                    Biometric::FACIAL_RECOGNITION_DATA_LEFT_JAW,
                    Biometric::FACIAL_RECOGNITION_DATA_RIGHT_JAW,
                ]),
                'data' => $this->faker->sha256(),
                'created_at' => now()->toDateTimeString(),
            ]),
            'retina_scan_data' => json_encode([
                'type' => $this->faker->randomElement([
                    Biometric::RETINA_SCAN_DATA_LEFT_EYE,
                    Biometric::RETINA_SCAN_DATA_RIGHT_EYE,
                ]),
                'data' => $this->faker->sha256(),
                'created_at' => now()->toDateTimeString(),
            ]),
            'voice_recognition_data' => json_encode([
                'type' => $this->faker->randomElement([
                    Biometric::VOICE_RECOGNITION_DATA_LEFT_VOICE,
                    Biometric::VOICE_RECOGNITION_DATA_RIGHT_VOICE,
                ]),
                'data' => $this->faker->sha256(),
                'created_at' => now()->toDateTimeString(),
            ]),
            'medical_records' => [
                'medicalHistory' => $this->faker->randomElement([
                    Biometric::MEDICAL_HISTORY_NONE,
                    Biometric::MEDICAL_HISTORY_DIABETES,
                    Biometric::MEDICAL_HISTORY_HYPERTENSION,
                    Biometric::MEDICAL_HISTORY_HEART_DISEASE,
                    Biometric::MEDICAL_HISTORY_ASTHMA
                ]),
                'conditions' => $this->faker->randomElements([
                    Biometric::CONDITION_ACUTE,
                    Biometric::CONDITION_CHRONIC,
                    Biometric::CONDITION_AUTO_IMMUNE,
                    Biometric::CONDITION_NEUROLOGICAL,
                    Biometric::CONDITION_RESPIRATORY
                ], $this->faker->numberBetween(0, 3)),
                'treatments' => $this->faker->randomElements([
                    Biometric::TREATMENT_MEDICATION,
                    Biometric::TREATMENT_THERAPY,
                    Biometric::TREATMENT_LIFESTYLE
                ], $this->faker->numberBetween(0, 2)),
                'tests' => $this->faker->randomElements([
                    Biometric::TEST_BLOOD,
                    Biometric::TEST_URINE,
                    Biometric::TEST_IMAGING
                ], $this->faker->numberBetween(0, 2)),
                'vaccinations' => $this->faker->randomElements([
                    Biometric::VACCINE_INFLUENZA,
                    Biometric::VACCINE_COVID_19,
                    Biometric::VACCINE_TETANUS
                ], $this->faker->numberBetween(0, 2)),
                'medications' => $this->faker->randomElements([
                    Biometric::MEDICATION_ANALGESIC,
                    Biometric::MEDICATION_ANTIBIOTIC,
                    Biometric::MEDICATION_ANTIHISTAMINE
                ], $this->faker->numberBetween(0, 2)),
                'allergies' => $this->faker->randomElements([
                    Biometric::ALLERGENS_POLLEN,
                    Biometric::ALLERGENS_PEANUTS,
                    Biometric::ALLERGENS_DUST,
                    Biometric::ALLERGENS_ANIMAL_DANDER
                ], $this->faker->numberBetween(0, 2)),
                'procedures' => $this->faker->randomElements([
                    Biometric::PROCEDURE_DIAGNOSTIC,
                    Biometric::PROCEDURE_THERAPEUTIC,
                    Biometric::PROCEDURE_PREVENTIVE
                ], $this->faker->numberBetween(0, 2)),
                'notes' => $this->faker->optional(0.3)->sentence(),
                'severity' => $this->faker->randomElement([
                    Biometric::SEVERITY_MILD,
                    Biometric::SEVERITY_MODERATE,
                    Biometric::SEVERITY_SEVERE
                ])
            ],
            'last_updated_at' => $this->faker->dateTimeThisYear(),
            '_status' => $this->faker->randomElement([
                Biometric::STATUS_VERIFIED,
                Biometric::STATUS_PENDING,
            ]),
            'metadata' => $this->faker->optional(0.5)->passthrough([
                'last_updated_at' => $this->faker->dateTimeThisYear(),
            ]),
        ];
    }

    /**
     * Indicate that the biometric record is verified.
     */
    public function verified(): static
    {
        return $this->state([
            '_status' => Biometric::STATUS_VERIFIED,
        ]);
    }

    /**
     * Indicate that the biometric record is pending.
     */
    public function pending(): static
    {
        return $this->state([
            '_status' => Biometric::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the biometric record is rejected.
     */
    public function rejected(): static
    {
        return $this->state([
            '_status' => Biometric::STATUS_REJECTED,
        ]);
    }

    /**
     * Indicate that the biometric record is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Get a random blood type.
     */
    protected function getRandomBloodType(): int
    {
        return $this->faker->randomElement([
            Biometric::BLOOD_TYPE_A_POSITIVE,
            Biometric::BLOOD_TYPE_A_NEGATIVE,
            Biometric::BLOOD_TYPE_B_POSITIVE,
            Biometric::BLOOD_TYPE_B_NEGATIVE,
            Biometric::BLOOD_TYPE_AB_POSITIVE,
            Biometric::BLOOD_TYPE_AB_NEGATIVE,
            Biometric::BLOOD_TYPE_O_POSITIVE,
            Biometric::BLOOD_TYPE_O_NEGATIVE,
        ]);
    }

    /**
     * Get a random body build type.
     */
    protected function getRandomBodyBuildType(): int
    {
        return $this->faker->randomElement([
            Biometric::BODY_BUILD_TYPE_SLIM,
            Biometric::BODY_BUILD_TYPE_AVERAGE,
            Biometric::BODY_BUILD_TYPE_ATHLETIC,
            Biometric::BODY_BUILD_TYPE_MUSCULAR,
            Biometric::BODY_BUILD_TYPE_OVERWEIGHT,
            Biometric::BODY_BUILD_TYPE_OBESE,
        ]);
    }

    /**
     * Get random distinguishing features.
     */
    protected function getRandomDistinguishingFeatures(): array
    {
        $features = [
            Biometric::DISTINGUISHING_FEATURES_TATTOOS,
            Biometric::DISTINGUISHING_FEATURES_SCARS,
            Biometric::DISTINGUISHING_FEATURES_BIRTHMARKS,
            Biometric::DISTINGUISHING_FEATURES_OTHER,
        ];

        return $this->faker->randomElements(
            $features,
            $this->faker->numberBetween(0, count($features))
        );
    }

}
