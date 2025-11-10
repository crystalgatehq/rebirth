<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Remove model imports to prevent dependency during migration
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define default values
        $defaultPermissions = [
            'can_invite' => false,
            'can_manage_members' => false,
            'can_edit_team' => false,
            'can_delete_team' => false,
            'can_manage_roles' => false,
            'can_manage_settings' => false,
            'can_manage_billing' => false,
            'can_export_data' => false
        ];

        $defaultMetadata = [
            'invitation_token' => null,
            'invited_by' => null,
            'invited_at' => null,
            'joined_at' => null,
            'last_active_at' => null,
            'mfa_enabled' => false,
            'mfa_method' => null,
            'timezone' => 'Africa/Nairobi',
            'preferences' => [
                'notifications' => [
                    'email' => true,
                    'in_app' => true,
                    'push' => true
                ],
                'language' => 'en',
                'theme' => 'system'
            ]
        ];

        $defaultTemporal = [
            'is_temporary' => false,
            'starts_at' => null,
            'expires_at' => null,
            'time_restrictions' => [
                'enabled' => false,
                'timezone' => 'Africa/Nairobi',
                'schedule' => []
            ]
        ];
        
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Role and permissions
            $table->enum('role', ['owner', 'member'])->default('member');
            $table->json('permissions')->nullable();
            
            // Membership details
            $table->json('metadata')->nullable();
            
            // Temporal constraints
            $table->json('temporal')->nullable();
            $table->timestamp('expires_at')->nullable(); // Explicit column for indexing
            
            // Status
            $table->enum('_status', [
                'invited',      // Invitation sent
                'active',       // Active member
                'suspended',    // Temporarily suspended
                'inactive',     // Inactive
                'banned'        // Banned from team
            ])->default('invited');
            
            $table->text('status_reason')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->foreignId('status_changed_by')->nullable()->constrained('users');
            
            // Audit fields
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('set null');
            $table->longText('notes')->nullable();
            
            // Audit fields
            $table->json('constraints')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->useCurrent();
            $table->text('assignment_notes')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->integer('_status')->default(RoleUser::STATUS_ACTIVE);
            $table->timestamps();
            
            // Unique constraint
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes
            $table->index('team_id');
            $table->index('user_id');
            $table->index('role');
            $table->index('_status');
            $table->index('expires_at');
            $table->index(['user_id', '_status']);
            $table->index(['team_id', '_status']);
            $table->index(['_status', 'expires_at']);
        });

        // Update existing records with default values
        if (Schema::hasTable('team_user')) {
            // Handle each field separately to avoid parameter binding issues
            $updateData = [
                'permissions' => json_encode($defaultPermissions),
                'metadata' => json_encode($defaultMetadata),
                'temporal' => json_encode($defaultTemporal)
            ];
            
            // Update null permissions
            DB::table('team_user')
                ->whereNull('permissions')
                ->update(['permissions' => $updateData['permissions']]);
                
            // Update null metadata
            DB::table('team_user')
                ->whereNull('metadata')
                ->update(['metadata' => $updateData['metadata']]);
                
            // Update null temporal
            DB::table('team_user')
                ->whereNull('temporal')
                ->update(['temporal' => $updateData['temporal']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }
};
