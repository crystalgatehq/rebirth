<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model import to prevent dependency during migration
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Default configuration
        $defaultConfig = [
            'sms' => true,
            'email' => true,
            'whatsapp' => true,
        ];
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_id')->constrained('factories')->onDelete('cascade');
            $table->string('farmer_code')->unique();
            $table->boolean('can_borrow')->default(false);
            $table->string('centre_code')->nullable();
            $table->string('centre_name')->nullable();
            $table->string('id_number')->nullable();
            $table->string('name');
            $table->string('phone');
            $table->string('route_code')->nullable();
            $table->string('route_name')->nullable();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->tinyInteger('_status')->default(1); // 1 = active
            $table->json('configuration')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('factory_id');
            $table->index('farmer_code');
            $table->index('centre_code');
            $table->index('route_code');
            $table->index('_slug');
            $table->index('_status');
        });

        // Update existing records with default configuration
        if (Schema::hasTable('farmers')) {
            DB::table('farmers')
                ->whereNull('configuration')
                ->update(['configuration' => json_encode($defaultConfig)]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};
