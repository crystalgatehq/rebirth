<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Produce;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produce', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->string('transaction_id');
            $table->timestamp('trans_time');
            $table->string('trans_code');
            $table->string('route_code')->nullable();
            $table->string('route_name')->nullable();
            $table->string('centre_code')->nullable();
            $table->string('centre_name')->nullable();
            $table->decimal('net_units', 10, 2);
            $table->decimal('payment_rate', 10, 2);
            $table->decimal('gross_pay', 10, 2);
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->decimal('transport_recovery', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->tinyInteger('status')->default(Produce::STATUS_ACTIVE); // 1 = active
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('uuid');
            $table->index('farmer_id');
            $table->index('factory_id');
            $table->index('transaction_id');
            $table->index('route_code');
            $table->index('route_name');
            $table->index('centre_code');
            $table->index('centre_name');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produce');
    }
};
