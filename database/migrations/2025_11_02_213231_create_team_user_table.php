<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\TeamUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Audit fields
            $table->json('constraints')->nullable();
            // Role and permissions
            $table->enum('play', [TeamUser::OWNER, TeamUser::MEMBER])->default(TeamUser::MEMBER);
            $table->json('activities')->nullable();

            $table->integer('status')->default(TeamUser::STATUS_ACTIVE);

            // Unique constraint
            $table->softDeletes();
            $table->timestamps();

            // Composite unique constraint for team membership
            $table->unique(['team_id', 'user_id']);
            
            // Optimized Indexes
            $table->index(['user_id', 'team_id']);
            $table->index('play');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
