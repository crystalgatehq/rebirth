<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\FarmerWallet;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('farmer_id')->unique()->constrained('farmers')->onDelete('cascade');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('loan_limit', 10, 2)->default(0);
            $table->decimal('borrowed_amount', 10, 2)->default(0);
            $table->decimal('available_earnings', 10, 2)->default(0);
            $table->tinyInteger('status')->default(FarmerWallet::STATUS_ACTIVE); // 1 = active
            $table->softDeletes();
            $table->timestamps();
            
            // Optimized Indexes
            $table->index('factory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_wallets');
    }
};
