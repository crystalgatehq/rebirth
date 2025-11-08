<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Add team and profile photo fields to the users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add current_team_id as a foreign key to teams table
            $table->foreignId('current_team_id')
                ->nullable()
                ->after('remember_token')
                ->constrained('teams')
                ->nullOnDelete();
                
            // Add profile_photo_path for storing user profile photos
            $table->string('profile_photo_path', 2048)
                ->nullable()
                ->after('current_team_id')
                ->comment('The path to the user\'s profile photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    /**
     * Reverse the changes made in the up method.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key constraint first
            if (Schema::hasColumn('users', 'current_team_id')) {
                $table->dropForeign(['current_team_id']);
                $table->dropColumn('current_team_id');
            }
            
            // Drop the profile photo path column
            if (Schema::hasColumn('users', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
        });
    }
};
