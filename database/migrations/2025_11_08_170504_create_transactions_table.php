<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('charge', 10, 2)->default(0);
            $table->decimal('convenience_fee', 10, 2)->default(0);
            $table->decimal('interest', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->longText('description')->nullable();
            $table->enum('system', ['app', 'ussd'])->default('app');
            $table->timestamp('loan_date');
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('_uuid');
            $table->index('farmer_id');
            $table->index('factory_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
