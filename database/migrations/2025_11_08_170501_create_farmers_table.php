<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Farmer;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->string('farmer_code'); // Removed ->unique() from here
            $table->boolean('can_borrow')->default(false);
            $table->string('centre_code')->nullable();
            $table->string('centre_name')->nullable();
            $table->string('id_number')->nullable();
            $table->string('name');
            $table->string('phone', 20)->nullable()->unique(); // E.164: +2547...
            $table->string('route_code')->nullable();
            $table->string('route_name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->json('configuration')->nullable()->comment('Default: {"sms":true,"email":false,"whatsapp":false}');
            $table->tinyInteger('status')->default(Farmer::STATUS_ACTIVE); // 1 = active
            $table->softDeletes();
            $table->timestamps();

            // Composite unique constraint - farmer_code must be unique within each factory
            $table->unique(['factory_id', 'farmer_code']);

            // Optimized Indexes
            $table->index('phone');
            $table->index('slug');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};

