<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('_slug')->nullable();
            $table->text('description')->nullable();
            
            // Ownership and access control
            $table->enum('visibility', ['private', 'team', 'public'])->default('private'); // private: Only me, team: Me and my team, public: Me and my team and Everyone in system
            
            // Status and timestamps
            $table->tinyInteger('_status')->default(1); // 1 = active, 0 = inactive
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('_uuid');
            $table->index('user_id');
            $table->index('name');
            $table->index('_slug');
            $table->index('_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_groups');
    }
};
