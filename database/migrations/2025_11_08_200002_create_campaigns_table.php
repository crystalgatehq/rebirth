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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('campaigns')->onDelete('cascade');
            $table->string('name');
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->json('goals')->nullable();
            $table->json('target_audience')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            $table->decimal('total_sent', 10, 2)->nullable();
            $table->decimal('total_delivered', 10, 2)->nullable();
            $table->decimal('total_failed', 10, 2)->nullable();
            $table->decimal('total_unsent', 10, 2)->nullable();
            $table->json('configuration')->nullable();
            $table->tinyInteger('_status')->default(1); // 1 = draft
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index('user_id');
            $table->index('team_id');
            $table->index('parent_id');
            $table->index('_slug');
            $table->index('_status');
        });

        // Set default values for JSON columns
        if (Schema::hasTable('campaigns')) {
            $defaultGoals = [
                'total_sent' => 0,
                'total_delivered' => 0,
                'total_failed' => 0,
                'total_unsent' => 0,
            ];
            
            $defaultTargetAudience = [
                'total_recipients' => 0,
                'total_delivered' => 0,
                'total_failed' => 0,
                'total_unsent' => 0,
            ];
            
            $defaultConfig = [
                'email' => false,
                'sms' => true,
                'whatsapp' => false,
            ];
            
            // Update existing records with default values
            $updateData = [
                'goals' => json_encode($defaultGoals),
                'target_audience' => json_encode($defaultTargetAudience),
                'configuration' => json_encode($defaultConfig)
            ];
            
            // Update null goals
            DB::table('campaigns')
                ->whereNull('goals')
                ->update(['goals' => $updateData['goals']]);
                
            // Update null target_audience
            DB::table('campaigns')
                ->whereNull('target_audience')
                ->update(['target_audience' => $updateData['target_audience']]);
                
            // Update null configuration
            DB::table('campaigns')
                ->whereNull('configuration')
                ->update(['configuration' => $updateData['configuration']]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};