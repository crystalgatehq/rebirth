<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CommunicationType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Core type information
            $table->string('name')->unique(); // Email, SMS, Voice, Push, etc.
            $table->string('slug')->unique();
            $table->string('icon')->default('message-square');
            $table->string('color')->default('#3b82f6');
            $table->longText('description')->nullable();
            
            // Type configuration
            $table->json('configuration')->nullable();
            
            // Status
            $table->tinyInteger('status')->default(CommunicationType::STATUS_ACTIVE); // 1 = active
            
            $table->softDeletes();
            $table->timestamps();

            // Optimized Indexes
            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_types');
    }
};