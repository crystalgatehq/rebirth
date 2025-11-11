<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Team;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Core team information
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('abbrivated_name')->nullable()->unique(); // Short team code (e.g., "DEV", "MKTG")
            $table->string('slug')->nullable()->unique();
            
            // Team detailss
            $table->longText('description')->nullable();
            $table->boolean('personal_team')->default(false);
            $table->tinyInteger('status')->default(Team::STATUS_ACTIVE); // 1 = active, 0 = inactive
            
            // Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Optimized Indexes
            $table->index('owner_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
