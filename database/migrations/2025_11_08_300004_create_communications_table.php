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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            
            // Core relationships
            $table->foreignId('communication_type_id')->constrained('communication_types');
            $table->foreignId('communication_category_id')->constrained('communication_categories');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('communications')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Campaign-specific fields
            $table->string('variant_name')->nullable(); // A/B testing variant name
            $table->json('variant_metrics')->nullable(); // Performance metrics for this variant
            
            // Message content (can override category template)
            $table->longText('content');
            $table->json('variables')->nullable(); // For template variables
            $table->json('attachments')->nullable(); // Array of file paths or URLs
            
            // Delivery configuration
            $table->enum('delivery_type', ['IMMEDIATE', 'SCHEDULED', 'RECURRING'])->default('IMMEDIATE');
            $table->timestamp('scheduled_for')->nullable();
            $table->json('recurrence')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            
            // Delivery metrics
            $table->integer('total_recipients')->default(0);
            $table->integer('successful_deliveries')->default(0);
            $table->integer('failed_deliveries')->default(0);
            $table->json('delivery_errors')->nullable();
            
            // Audit timestamps
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Metadata
            $table->string('_slug')->unique();
            $table->json('metadata')->nullable();

            // Status tracking
            $table->enum('_status', [
                'DRAFT', 'PENDING_APPROVAL', 'APPROVED', 'PROCESSING',
                'SENT', 'PARTIALLY_SENT', 'FAILED', 'CANCELLED'
            ])->default('DRAFT');
            
            // Status and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index(['communication_type_id', '_status', 'scheduled_for']);
            $table->index(['campaign_id', '_status']);
            $table->index('created_by');
        });

        // Set default values for JSON columns
        if (Schema::hasTable('communications')) {
            // Default recurrence
            $defaultRecurrence = [
                'frequency' => null,
                'days' => [],
                'end_type' => 'never',
                'end_value' => null
            ];
            
            // Default metadata
            $defaultMetadata = [
                'source' => 'MANUAL',
                'ip_address' => null,
                'user_agent' => null,
                'campaign_id' => null
            ];
            
            // Update null values with defaults
            DB::table('communications')
                ->whereNull('recurrence')
                ->update(['recurrence' => json_encode($defaultRecurrence)]);
                
            DB::table('communications')
                ->whereNull('metadata')
                ->update(['metadata' => json_encode($defaultMetadata)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};