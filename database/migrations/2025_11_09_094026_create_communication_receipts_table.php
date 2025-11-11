<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CommunicationReceipt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('communication_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Core Relationships            
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('cascade');
            $table->foreignId('communication_type_id')->nullable()->constrained('communication_types')->onDelete('set null');
            $table->foreignId('communication_category_id')->nullable()->constrained('communication_categories')->onDelete('set null');
            $table->foreignId('communication_id')->constrained('communications')->onDelete('cascade');
            $table->foreignId('contact_group_id')->nullable()->constrained('contact_groups')->onDelete('set null');
            $table->foreignId('contact_group_subscriber_id')->nullable()->constrained('contact_group_subscribers')->onDelete('set null');

            // Recipient Information
            $table->string('recipient_telephone');
            $table->string('recipient_name')->nullable();
            
            // Message Content
            $table->longText('template_content');
            $table->string('template_identifier')->nullable(); // Identifier for the template used
            $table->json('template_variables')->nullable(); // Variables used to generate the message
            
            // Sender Information
            $table->string('sender_id')->nullable();
            $table->string('sender_name')->nullable();
            
            // Provider Information
            // e.g., 'africastalking', 'twilio', 'nexmo' etc.
            $table->tinyInteger('provider')->default(CommunicationReceipt::PROVIDER_AFRICASTALKING);
            $table->string('provider_message_id')->nullable()->unique(); // External provider's message ID
            $table->json('provider_response')->nullable(); // Raw response from provider
            
            // Delivery Tracking
            $table->integer('retry_count')->default(0);
            $table->longText('error_message')->nullable();
            $table->json('delivery_errors')->nullable();
            
            // Timestamps
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('processing_completed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            // Cost Information
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('currency', 3)->default('KES');
            
            // Metadata
            $table->json('metadata')->nullable();

            // Status Tracking
            $table->tinyInteger('status')->default(CommunicationReceipt::STATUS_PENDING); // Initial state

            // Soft Deletes & Timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index(['communication_id', 'status']);
            // $table->index(['farmer_id', 'status']);
            $table->index(['campaign_id', 'status']);
            $table->index('recipient_telephone');
            $table->index('provider_message_id');
            $table->index('created_at');
            $table->index('sent_at');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_receipts');
    }
};
