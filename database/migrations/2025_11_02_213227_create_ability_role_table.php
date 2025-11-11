<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AbilityRole;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ability_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ability_id')->constrained('abilities')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');

            // Audit fields
            $table->json('constraints')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('granted_at')->useCurrent();
            $table->longText('grant_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->integer('status')->default(AbilityRole::STATUS_ACTIVE);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['ability_id', 'role_id']);
            
            // Optimized Indexes
            $table->index('role_id');
            $table->index('granted_by');
            $table->index(['ability_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ability_role');
    }
};
