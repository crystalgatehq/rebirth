<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ContactGroupSubscriber;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_group_subscribers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('contact_group_id')->constrained('contact_groups')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Timestamp of the last communication sent to this subscriber through this group
            $table->timestamp('last_contacted_at')->nullable();
            
            // Metadata for additional information
            $table->json('metadata')->nullable(); // Custom fields, notes, etc.
            
            // Status: 1 = active, 0 = inactive, 2 = pending, 3 = rejected
            $table->tinyInteger('status')->default(ContactGroupSubscriber::STATUS_ACTIVE);
            
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('uuid');
            $table->index('contact_group_id');
            $table->index('farmer_id');
            $table->index('added_by');
            $table->index('status');
            
            // Ensure a farmer can't be added to the same group twice
            $table->unique(['contact_group_id', 'farmer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_group_subscribers');
    }
};