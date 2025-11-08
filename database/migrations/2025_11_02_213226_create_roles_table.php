<?php

use App\Models\Role;
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
            $table->string('name')->unique();
            $table->string('_slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->integer('_hierarchy_matrix_level')->default(0);
            $table->tinyInteger('_status')->default(Role::PENDING);
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
        Schema::dropIfExists('roles');
    }
};
