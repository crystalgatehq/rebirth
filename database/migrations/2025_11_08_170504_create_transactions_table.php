<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('charge', 10, 2)->default(0);
            $table->decimal('convenience_fee', 10, 2)->default(0);
            $table->decimal('interest', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->longText('description')->nullable();
            $table->timestamp('loan_date');
            $table->tinyInteger('system')->default(Transaction::SYSTEM_APP);
            $table->tinyInteger('status')->default(Transaction::STATUS_PENDING);
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('uuid');
            $table->index('farmer_id');
            $table->index('factory_id');
            $table->index('system');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
