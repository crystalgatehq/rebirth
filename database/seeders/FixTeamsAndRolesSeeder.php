<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixTeamsAndRolesSeeder extends Seeder
{
    public function run()
    {
        // Disable mass assignment protection
        Team::unguard();
        
        // Get all roles
        $roles = Role::orderBy('_hierarchy_matrix_level', 'desc')->get();
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // First, clear all role assignments
        DB::table('role_user')->truncate();
        
        // Then, delete all teams and team_user entries
        DB::table('team_user')->truncate();
        DB::table('teams')->truncate();
        
        // Clean up biometrics table - keep only one record per user
        $users = DB::table('users')->pluck('id');
        
        foreach ($users as $userId) {
            // Get all biometrics for this user except the first one
            // Using raw SQL for MySQL compatibility with OFFSET
            $duplicates = DB::select(
                "SELECT id FROM biometrics WHERE user_id = ? AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM biometrics 
                        WHERE user_id = ? 
                        ORDER BY id ASC 
                        LIMIT 1
                    ) t
                )", 
                [$userId, $userId]
            );
            
            // Convert to array of IDs
            $duplicateIds = collect($duplicates)->pluck('id')->toArray();
                
            if (!empty($duplicateIds)) {
                // Delete duplicate biometrics
                DB::table('biometrics')->whereIn('id', $duplicateIds)->delete();
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Track created teams to prevent duplicates
        $createdTeams = [];
        
        // Ensure all team_user entries only have 'owner' or 'member' roles
        DB::table('team_user')
            ->whereNotIn('role', ['owner', 'member'])
            ->update(['role' => 'member']);
        
        // First, assign each user to exactly one role based on email
        $users = User::all();
        
        // First clear all role assignments
        DB::table('role_user')->truncate();
        
        foreach ($users as $user) {
            // Get the role name from the email (before @)
            $emailParts = explode('@', $user->email);
            $emailPrefix = $emailParts[0];
            
            // Handle special cases (like 'super.administrator' -> 'super administrator')
            $roleName = str_replace('.', ' ', $emailPrefix);
            $roleName = str_replace('_', ' ', $roleName);
            $roleName = ucwords($roleName);
            
            // Special case for 'maitre dhotel'
            $roleName = str_replace('maitre dhotel', 'maître d’hôtel', strtolower($roleName));
            $roleName = ucwords($roleName);
            
            // Find the role by name
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                // Try alternative naming (singular/plural)
                $role = Role::where('name', Str::singular($roleName))->first() ?: 
                        Role::where('name', Str::plural($roleName))->first();
            }
            
            if ($role) {
                // Use sync without detaching to ensure only one role per user
                $user->roles()->syncWithoutDetaching([$role->id]);
                $this->command->info("Assigned role '{$role->name}' to user '{$user->email}'");
            } else {
                $this->command->warn("No matching role found for user: {$user->email} (tried: {$roleName})");
            }
        }
        
        // Now create teams and assign users
        $roles->each(function ($role) {
            // Find the default user for this role
            $defaultEmail = strtolower(str_replace(' ', '.', $role->name)) . '@psilocybin.org';
            $defaultUser = User::where('email', $defaultEmail)->first();
            
            if (!$defaultUser) {
                $this->command->warn("Default user not found for role: {$role->name}");
                return;
            }
            
            // Create team name with role ID to ensure uniqueness
            $teamName = \Illuminate\Support\Str::plural($role->name) . " ({$role->id})";
            
            // Check if we've already created a team for this role
            if (isset($createdTeams[$role->id])) {
                $team = $createdTeams[$role->id];
            } else {
                // Create the team if it doesn't exist
                $team = Team::create([
                    '_uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'owner_id' => $defaultUser->id,
                    'name' => \Illuminate\Support\Str::plural($role->name),
                    'display_name' => \Illuminate\Support\Str::plural($role->name),
                    '_slug' => \Illuminate\Support\Str::slug(\Illuminate\Support\Str::plural($role->name), '_'),
                    'code' => strtoupper(substr(\Illuminate\Support\Str::plural($role->name), 0, 4)),
                    'description' => "Team for {$role->name} role",
                    'type' => 'team',
                    'is_active' => true,
                    'is_verified' => false,
                    '_status' => $role->_status === Role::ACTIVE
                        ? 1 // Team::ACTIVE
                        : 2, // Team::SUSPENDED
                    'settings' => json_encode([
                        'privacy' => 'private',
                        'join_policy' => 'invite_only',
                        'visibility' => [
                            'directory' => true,
                            'search' => true,
                            'profile' => 'public'
                        ],
                        'notifications' => [
                            'new_member' => true,
                            'role_changes' => true,
                            'settings_changes' => true
                        ],
                        'security' => [
                            'require_2fa' => false,
                            'session_timeout' => 120,
                            'ip_restrictions' => []
                        ]
                    ]),
                    'communication_settings' => json_encode([
                        'default_channels' => [
                            'email' => true,
                            'slack' => false,
                            'teams' => false,
                            'discord' => false
                        ],
                        'announcements' => [
                            'require_approval' => true,
                            'allowed_roles' => ['admin', 'manager']
                        ],
                        'messaging' => [
                            'allow_direct_messages' => true,
                            'allow_group_chats' => true,
                            'max_group_size' => 50,
                            'message_retention_days' => 365
                        ]
                    ]),
                ]);
                
                // Store the created team to prevent duplicates
                $createdTeams[$role->id] = $team;
            }
            
            // Clear existing team memberships for this team
            DB::table('team_user')->where('team_id', $team->id)->delete();
            
            // Add the default user as owner with full record
            DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $defaultUser->id,
                'role' => 'owner',
                'permissions' => json_encode([
                    'can_invite' => true,
                    'can_manage_members' => true,
                    'can_edit_team' => true,
                    'can_delete_team' => true,
                    'can_manage_roles' => true,
                    'can_manage_settings' => true,
                    'can_manage_billing' => true,
                    'can_export_data' => true
                ]),
                'metadata' => json_encode([
                    'invitation_token' => null,
                    'invited_by' => null,
                    'invited_at' => null,
                    'joined_at' => now(),
                    'last_active_at' => now(),
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
                ]),
                'temporal' => json_encode([
                    'is_temporary' => false,
                    'starts_at' => null,
                    'expires_at' => null,
                    'time_restrictions' => [
                        'enabled' => false,
                        'timezone' => 'Africa/Nairobi',
                        'schedule' => []
                    ]
                ]),
                '_status' => 'active',
                'added_by' => $defaultUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Role is already assigned in the first pass
            
            // Find and assign other users with the same role as members
            $userRoleSlug = strtolower(str_replace(' ', '.', $role->name));
            $otherUsers = User::where('email', 'LIKE', "%{$userRoleSlug}@psilocybin.org")
                ->where('id', '!=', $defaultUser->id)
                ->get();
                
            foreach ($otherUsers as $user) {
                // Attach user to team as member with full record
                DB::table('team_user')->insert([
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'permissions' => json_encode([
                        'can_invite' => false,
                        'can_manage_members' => false,
                        'can_edit_team' => false,
                        'can_delete_team' => false,
                        'can_manage_roles' => false,
                        'can_manage_settings' => false,
                        'can_manage_billing' => false,
                        'can_export_data' => false
                    ]),
                    'metadata' => json_encode([
                        'invitation_token' => null,
                        'invited_by' => null,
                        'invited_at' => null,
                        'joined_at' => now(),
                        'last_active_at' => now(),
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
                    ]),
                    'temporal' => json_encode([
                        'is_temporary' => false,
                        'starts_at' => null,
                        'expires_at' => null,
                        'time_restrictions' => [
                            'enabled' => false,
                            'timezone' => 'Africa/Nairobi',
                            'schedule' => []
                        ]
                    ]),
                    '_status' => 'active',
                    'added_by' => $defaultUser->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Role is already assigned in the first pass
            }
            
            $this->command->info("Created team '{$team->name}' with " . ($otherUsers->count() + 1) . " members");
        });
        
        // Re-enable mass assignment protection
        Team::reguard();
        
        $this->command->info('Teams and role assignments have been fixed.');
    }
}
