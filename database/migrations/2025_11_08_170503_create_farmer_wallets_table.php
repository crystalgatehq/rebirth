<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('farmer_id')->unique()->constrained('farmers')->onDelete('cascade');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('loan_limit', 10, 2)->default(0);
            $table->decimal('borrowed_amount', 10, 2)->default(0);
            $table->decimal('available_earnings', 10, 2)->default(0);
            $table->tinyInteger('_status')->default(1); // 1 = active
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('_uuid');
            $table->index('farmer_id');
            $table->index('factory_id');
            $table->index('_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_wallets');
    }
};
