<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ability;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {        
        // Create the table with nullable JSON columns
        Schema::create('abilities', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->tinyInteger('_status')->default(Ability::STATUS_ACTIVE); // 1 = active, 0 = inactive
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_uuid');
            $table->index('_slug');
            $table->index('_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abilities');
    }
};
