<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->tinyInteger('_status')->default(Role::ACTIVE); // 1 = active, 0 = inactive
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
        Schema::dropIfExists('roles');
    }
};
