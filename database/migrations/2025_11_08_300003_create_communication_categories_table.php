<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CommunicationCategory;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('communication_type_id')->constrained('communication_types')->onDelete('cascade');
            
            // Category information
            $table->string('name'); // e.g., Marketing, Transactional, Alerts, Notifications
            $table->string('slug')->unique();
            $table->string('icon')->default('folder');
            $table->string('color')->default('#6b7280');
            $table->longText('description')->nullable();
            
            // Templates by communication type
            $table->json('template')->nullable();
            
            // Hierarchy
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('communication_categories')
                ->onDelete('set null');
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Category configuration
            $table->json('configuration')->nullable();
            
            // Status
            $table->tinyInteger('status')->default(CommunicationCategory::STATUS_ACTIVE); // 1 = active

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('uuid');
            $table->index('slug');
            $table->index('status');
            $table->index('parent_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_categories');
    }
};