<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->text('default_value')->nullable();
            $table->text('current_value')->nullable();
            $table->string('data_type')->default('string');
            $table->string('group')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false);
            $table->json('options')->nullable();
            $table->tinyInteger('status')->default(Setting::STATUS_ACTIVE); // 1 = active, 0 = inactive
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('uuid');
            $table->index('slug');
            $table->index('data_type');
            $table->index('group');
            $table->index('status');
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
