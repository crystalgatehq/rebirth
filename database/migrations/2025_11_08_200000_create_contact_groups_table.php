<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ContactGroup;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            
            // Ownership and access control
            $table->tinyInteger('visibility')->default(ContactGroup::VISIBILITY_PRIVATE); // private: Only me, team: Me and my team, public: Me and my team and Everyone in system
            
            // Status and timestamps
            $table->tinyInteger('status')->default(ContactGroup::STATUS_ACTIVE); // 1 = active, 0 = inactive
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('uuid');
            $table->index('user_id');
            $table->index('name');
            $table->index('slug');
            $table->index('visibility');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_groups');
    }
};
