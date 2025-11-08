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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->text('default_value')->nullable();
            $table->text('current_value')->nullable();
            $table->string('data_type')->default('string');
            $table->string('group')->default('general');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false);
            $table->json('options')->nullable();
            $table->timestamps();
            
            $table->index(['group', 'name', '_slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
