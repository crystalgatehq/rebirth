<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Communication;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Core relationships
            $table->foreignId('communication_type_id')->constrained('communication_types');
            $table->foreignId('communication_category_id')->constrained('communication_categories');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('communications')->onDelete('cascade');
            $table->foreignId('contact_group_id')->nullable()->constrained('contact_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Campaign-specific fields
            $table->string('variant_name')->nullable(); // A/B testing variant name
            $table->json('variant_metrics')->nullable(); // Performance metrics for this variant
            
            // Message content (can override category template)
            $table->string('subject')->nullable();
            $table->longText('content');
            $table->json('variables')->nullable(); // For template variables
            $table->json('attachments')->nullable(); // Array of file paths or URLs
            
            // Delivery configuration (1=immediate, 2=scheduled, 3=recurring)
            $table->tinyInteger('delivery_type')->default(Communication::DELIVERY_TYPE_IMMEDIATE); // 1 = immediate
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
            $table->timestamp('last_processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Metadata
            $table->string('slug')->unique();
            $table->json('metadata')->nullable();

            // Status tracking (1=draft, 2=pending_approval, 3=approved, 4=processing, 5=sent, 6=partially_sent, 7=failed, 8=cancelled)
            $table->tinyInteger('status')->default(Communication::STATUS_DRAFT); // 1 = draft
            
            // Status and timestamps
            $table->softDeletes();
            $table->timestamps();

            // Optimized Indexes
            $table->index(['communication_type_id', 'status', 'scheduled_for']);
            $table->index(['campaign_id', 'status']);
            $table->index('created_by');
            $table->index(['contact_group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};