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
        Schema::create('communication_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('communication_type_id')->constrained('communication_types')->onDelete('cascade');
            
            // Category information
            $table->string('name'); // e.g., Marketing, Transactional, Alerts, Notifications
            $table->string('_slug')->unique();
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
            $table->tinyInteger('_status')->default(1); // 1 = active

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index('_slug');
            $table->index('_status');
            $table->index('parent_id');
            $table->index('created_at');
        });

        // Set default configuration for communication categories
        if (Schema::hasTable('communication_categories')) {
            // Default template
            $defaultTemplate = [
                'message' => '',
                'variables' => []
            ];
            
            // Default configuration
            $defaultConfig = [
                'requires_approval' => false,
                'approval_roles' => [],
                'retention_days' => 365,
                'default_priority' => 'normal',
                'allowed_templates' => true,
                'allowed_attachments' => 10,
                'max_attachment_size' => 5
            ];
            
            // Update null templates and configurations
            DB::table('communication_categories')
                ->whereNull('template')
                ->update(['template' => json_encode($defaultTemplate)]);
                
            DB::table('communication_categories')
                ->whereNull('configuration')
                ->update(['configuration' => json_encode($defaultConfig)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_categories');
    }
};