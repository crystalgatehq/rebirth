<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\RoleUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Audit fields
            $table->json('constraints')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('assignment_notes')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->integer('_status')->default(RoleUser::STATUS_ACTIVE);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['role_id', 'user_id']);
            
            // Indexes
            $table->index('role_id');
            $table->index('user_id');
            $table->index('assigned_by');
            $table->index(['user_id', '_status']);
            $table->index(['role_id', '_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
