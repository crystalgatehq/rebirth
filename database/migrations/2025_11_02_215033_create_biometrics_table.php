<?php

use App\Models\Biometric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('biometrics', function (Blueprint $table) {
            // =================================================================
            // Core
            // =================================================================
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Reference to the user this biometric data belongs to');

            // =================================================================
            // Physical Characteristics
            // =================================================================
            $table->tinyInteger('blood_type')
                ->nullable()
                ->comment('Blood type of the individual');
                
            $table->unsignedTinyInteger('height_cm')
                ->nullable()
                ->comment('Height in centimeters');
                
            $table->decimal('weight_kg', 5, 2)
                ->unsigned()
                ->nullable()
                ->comment('Weight in kilograms');
                
            $table->tinyInteger('body_build_type')
                ->nullable()
                ->comment('General body build type');

            $table->json('distinguishing_features')
                ->nullable()
                ->comment('Notable physical features like scars, tattoos, birthmarks');

            // =================================================================
            // Facial Features
            // =================================================================
            $table->tinyInteger('eye_color')
                ->nullable()
                ->comment('Primary eye color');
                
            $table->tinyInteger('eye_shape')
                ->nullable()
                ->comment('Shape of the eyes');
                
            $table->tinyInteger('hair_color')
                ->nullable()
                ->comment('Natural hair color');
                
            $table->tinyInteger('skin_tone')
                ->nullable()
                ->comment('General skin tone classification');

            // =================================================================
            // Biometric Data
            // =================================================================
            $table->json('fingerprint_data')
                ->nullable()
                ->comment('Encrypted fingerprint data');
                
            $table->json('facial_recognition_data')
                ->nullable()
                ->comment('Encrypted facial recognition data');
                
            $table->json('retina_scan_data')
                ->nullable()
                ->comment('Encrypted retina scan data');

            $table->json('voice_recognition_data')
                ->nullable()
                ->comment('Encrypted voice recognition data');
                
            // =================================================================
            // Medical & Health Records (Consolidated into JSON for better structure)
            // =================================================================
            $table->json('medical_records')
                ->nullable()
                ->comment('{
                    "medicalHistory": "...",
                    "conditions": ["...", "..."],
                    "treatments": ["...", "..."],
                    "tests": ["...", "..."],
                    "vaccinations": ["...", "..."],
                    "medications": ["...", "..."],
                    "allergies": ["...", "..."],
                    "procedures": ["...", "..."],
                    "notes": "..."
                }');

            // =================================================================
            // Status & Metadata
            // =================================================================
            $table->timestamp('last_updated_at')
                ->useCurrent()
                ->comment('When these biometrics were last updated');

            $table->tinyInteger('_status')
                ->default(Biometric::STATUS_PENDING)
                ->comment('Status of the biometric record');
            
            $table->json('metadata')
                ->nullable()
                ->comment('Additional metadata in JSON format');

            // =================================================================
            // Timestamps & Soft Deletes
            // =================================================================
            $table->softDeletes();
            $table->timestamps();

            // =================================================================
            // Indexes
            // =================================================================
            $table->index('user_id');
            $table->index('last_updated_at');
            $table->index('_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometrics');
    }
};
