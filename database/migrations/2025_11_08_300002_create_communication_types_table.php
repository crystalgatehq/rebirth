<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            
            // Core type information
            $table->string('name')->unique(); // Email, SMS, Voice, Push, etc.
            $table->string('_slug')->unique();
            $table->string('icon')->default('message-square');
            $table->string('color')->default('#3b82f6');
            $table->longText('description')->nullable();
            
            // Type configuration
            $table->json('configuration')->nullable();
            
            // Status
            $table->tinyInteger('_status')->default(1); // 1 = active
            
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index('_slug');
            $table->index('_status');
        });

        // Set default configuration for communication types
        if (Schema::hasTable('communication_types')) {
            $defaultConfig = [
                'handler_service_class_path' => '\\App\\Services\\IAN\\AfricaIsTalkingServices',
                'supports_attachments' => false,
                'supported_template_structure' => [
                    'message' => 'string|max:255',
                    'variables' => 'array|max:255'
                ],
                'rate_limit' => [
                    'limit' => 1000,
                    'period' => 1440
                ]
            ];
            
            // Update null configurations
            DB::table('communication_types')
                ->whereNull('configuration')
                ->update(['configuration' => json_encode($defaultConfig)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_types');
    }
};