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
        Schema::table('roles', function (Blueprint $table) {
            // Add indexes
            $table->index('_uuid');
            $table->index('_slug');
            $table->index('_status');
            $table->index('type');
            $table->index('level');
            $table->index('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['_uuid']);
            $table->dropIndex(['_slug']);
            $table->dropIndex(['_status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['level']);
            $table->dropIndex(['team_id']);
        });
    }
};
