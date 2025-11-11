<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Factory;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factories', function (Blueprint $table) {
            $table->id();
            $table->integer('county_id')->constrained('counties')->onDelete('cascade');
            $table->string('factory_code')->unique();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->string('base_url');
            $table->string('api_user');
            $table->string('api_user_credentials');
            $table->tinyInteger('status')->default(Factory::STATUS_ACTIVE);
            $table->softDeletes();
            $table->timestamps();

            // Optimized Indexes
            $table->index('factory_code');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factories');
    }
};
