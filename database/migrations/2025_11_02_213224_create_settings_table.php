<?php

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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->text('default_value')->nullable();
            $table->text('current_value')->nullable();
            $table->string('data_type')->default('string');
            $table->string('group')->default('communication'); // Default to communication
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false);
            
            // Enhanced options for different setting types (no default value for JSON in MySQL)
            $table->json('options')->nullable();
            
            // Communication-specific metadata (no default value for JSON in MySQL)
            $table->json('metadata')->nullable();
            
            // Using raw value 1 for active status to avoid model dependency
            $table->tinyInteger('_status')->default(1); // 1 = active, 0 = inactive
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index('_slug');
            $table->index('_status');
            $table->index('group');
            $table->index('data_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
