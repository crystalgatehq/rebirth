<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Campaign;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('campaigns')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable()->unique();
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
            $table->tinyInteger('status')->default(Campaign::STATUS_DRAFT); // 1 = draft
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('uuid');
            $table->index('user_id');
            $table->index('team_id');
            $table->index('parent_id');
            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};