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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('_uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->boolean('personal_team')->default(false);
            $table->tinyInteger('_status')->default(0); // 0 = inactive, 1 = active
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('_status');
            $table->index('_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
