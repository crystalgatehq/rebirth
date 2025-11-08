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
        
        // Set default hierarchy levels after table is created
        $this->setDefaultHierarchyLevels();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
    
    /**
     * Set default hierarchy levels for roles
     */
    private function setDefaultHierarchyLevels()
    {
        $roles = [
            'super_administrator' => 100,
            'general_manager' => 90,
            'operations_manager' => 80,
            'finance_manager' => 80,
            'club_manager' => 70,
            'restaurant_manager' => 70,
            'bar_manager' => 70,
            'head_bartender' => 60,
            'bartender' => 50,
            'server' => 40,
            'bouncer' => 40,
            'host' => 40,
            'member' => 30,
            'guest' => 10,
        ];

        foreach ($roles as $slug => $level) {
            \DB::table('roles')
                ->where('_slug', $slug)
                ->update(['_hierarchy_matrix_level' => $level]);
        }
    }
};
