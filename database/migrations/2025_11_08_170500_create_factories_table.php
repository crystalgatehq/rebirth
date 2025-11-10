<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->string('factory_code')->unique();
            $table->string('name');
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->string('base_url');
            $table->tinyInteger('_status')->default(1); // 1 = active
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('factory_code');
            $table->index('_slug');
            $table->index('_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factories');
    }
};
