<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting database fix...');
        
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // 1. Fix role_user table (should have 55 records, one per user)
        $this->fixRoleUserTable();
        
        // 2. Fix teams table (should have 55 teams, one per role)
        $this->fixTeamsTable();
        
        // 3. Fix team_user table (should have 55 owners + 2970 members = 3025 records)
        $this->fixTeamUserTable();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Database fix completed.');
    }
    
    protected function fixRoleUserTable()
    {
        $this->command->info('Fixing role_user table...');
        
        // // Clear the table
        // DB::table('role_user')->truncate();
        
        // // Get all users and assign each to one role based on email
        // $users = User::all();
        
        // foreach ($users as $user) {
        //     // Get the role name from the email (before @)
        //     $emailParts = explode('@', $user->email);
        //     $emailPrefix = $emailParts[0];
            
        //     // Handle special cases (like 'super.administrator' -> 'super administrator')
        //     $roleName = str_replace('.', ' ', $emailPrefix);
        //     $roleName = str_replace('_', ' ', $roleName);
        //     $roleName = ucwords($roleName);
            
        //     // Special case for 'maitre dhotel'
        //     $roleName = str_replace('maitre dhotel', 'maître d’hôtel', strtolower($roleName));
        //     $roleName = ucwords($roleName);
            
        //     // Find the role by name
        //     $role = Role::where('name', $roleName)->first();
            
        //     if (!$role) {
        //         // Try alternative naming (singular/plural)
        //         $role = Role::where('name', Str::singular($roleName))->first() ?: 
        //                 Role::where('name', Str::plural($roleName))->first();
        //     }
            
        //     if ($role) {
        //         // Assign the role to the user
        //         DB::table('role_user')->insert([
        //             'role_id' => $role->id,
        //             'user_id' => $user->id,
        //             'created_at' => now(),
        //             'updated_at' => now()
        //         ]);
        //         $this->command->info("Assigned role '{$role->name}' to user '{$user->email}'");
        //     } else {
        //         $this->command->warn("No matching role found for user: {$user->email} (tried: {$roleName})");
        //     }
        // }
        
        $count = DB::table('role_user')->count();
        $this->command->info("role_user table now has {$count} records (expected: 55)");
    }
    
    protected function fixTeamsTable()
    {
        $this->command->info('Fixing teams table...');
        
        // Get all roles
        $roles = Role::all();
        
        // Keep track of valid team IDs
        $validTeamIds = [];
        
        // For each role, ensure we have exactly one team
        foreach ($roles as $role) {
            // Get or create team for this role
            $team = Team::firstOrCreate(
                ['name' => Str::plural($role->name)],
                [
                    '_uuid' => (string) Str::uuid(),
                    'owner_id' => null, // Will be updated later
                    'parent_team_id' => null, // No parent team by default
                    'name' => Str::plural($role->name),
                    'display_name' => Str::plural($role->name),
                    '_slug' => Str::slug(Str::plural($role->name)),
                    'code' => strtoupper(substr(Str::plural($role->name), 0, 4)),
                    'description' => "Team for {$role->name} role",
                    'personal_team' => false,
                    'logo_path' => null,
                    'banner_path' => null,
                    'website' => null,
                    'industry' => null,
                    'size' => 0, // Will be updated when members are added
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
                            'allowed_roles' => ['owner', 'members_with_permission']
                        ],
                        'messaging' => [
                            'allow_direct_messages' => true,
                            'allow_group_chats' => true,
                            'max_group_size' => 50,
                            'message_retention_days' => 365
                        ]
                    ]),
                    'type' => 'team',
                    'is_active' => true,
                    'is_verified' => false,
                    'verified_at' => null,
                    'verified_by' => null,
                    '_status' => Team::STATUS_ACTIVE,
                    'trial_ends_at' => null,
                ]
            );
            
            $validTeamIds[] = $team->id;
            $this->command->info("Ensured team exists for role: {$role->name}");
        }
        
        // Delete any teams that don't have a matching role or are duplicates
        $teamsToDelete = Team::whereNotIn('id', $validTeamIds)->get();
        
        foreach ($teamsToDelete as $team) {
            $this->command->warn("Deleting team '{$team->name}' (ID: {$team->id}) as it doesn't match any role");
            $team->delete();
        }
        
        $count = Team::count();
        $this->command->info("teams table now has {$count} records (expected: 55)");
    }
    
    protected function fixTeamUserTable()
    {
        $this->command->info('Fixing team_user table...');
        
        // Clear the table
        DB::table('team_user')->truncate();
        
        // Get all teams and users
        $teams = Team::all();
        $users = User::all();
        
        // For each team, assign the owner and members
        foreach ($teams as $team) {
            // Find the owner (user with matching role)
            $owner = $users->first(function($user) use ($team) {
                return $user->roles->contains('name', Str::singular($team->name));
            });
            
            if (!$owner) {
                $this->command->warn("No owner found for team '{$team->name}'. Using first user as owner.");
                $owner = $users->first();
            }
            
            // Update team owner if needed
            if ($team->owner_id !== $owner->id) {
                $team->owner_id = $owner->id;
                $team->save();
            }
            
            // Add owner to the team
            DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $owner->id,
                'role' => Team::OWNER,
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
                    'joined_at' => now()->toDateTimeString(),
                    'last_active_at' => now()->toDateTimeString(),
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
                '_status' => TeamUser::STATUS_ACTIVE,
                'status_reason' => null,
                'status_changed_at' => now()->toDateTimeString(),
                'status_changed_by' => $owner->id,
                'added_by' => $owner->id,
                'notes' => 'Team owner',
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ]);
            
            // Add all other users as members
            $otherUsers = $users->where('id', '!=', $owner->id);
            $memberData = [];
            
            foreach ($otherUsers as $user) {
                $memberData[] = [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'role' => Team::MEMBER,
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
                        'invited_by' => $owner->id,
                        'invited_at' => now()->toDateTimeString(),
                        'joined_at' => now()->toDateTimeString(),
                        'last_active_at' => now()->toDateTimeString(),
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
                    '_status' => TeamUser::STATUS_ACTIVE,
                    'status_reason' => 'Added during team setup',
                    'status_changed_at' => now()->toDateTimeString(),
                    'status_changed_by' => $owner->id,
                    'added_by' => $owner->id,
                    'notes' => 'Team member',
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString()
                ];
            }
            
            // Batch insert members
            if (!empty($memberData)) {
                DB::table('team_user')->insert($memberData);
            }
            
            $this->command->info("Team '{$team->name}' has 1 owner and {$otherUsers->count()} members");
        }
        
        // Verify counts
        $ownerCount = DB::table('team_user')->where('role', 'owner')->count();
        $memberCount = DB::table('team_user')->where('role', 'member')->count();
        $totalCount = $ownerCount + $memberCount;
        
        $this->command->info("team_user table now has {$totalCount} records (expected: 3025)");
        $this->command->info("- Owners: {$ownerCount} (expected: 55)");
        $this->command->info("- Members: {$memberCount} (expected: 2970)");
    }
}
