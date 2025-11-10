<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetupTeamStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear all team memberships and teams
        DB::table('team_user')->truncate();
        DB::table('teams')->truncate();
        
        // Get all roles and users
        $roles = Role::all();
        $users = User::all();
        
        if ($users->count() !== $roles->count()) {
            $this->command->error('Number of users does not match number of roles. Please run the database seeder first.');
            return;
        }
        
        $totalTeams = $roles->count();
        $totalUsers = $users->count();
        $expectedOwnerRecords = $totalTeams; // 55
        $expectedMemberRecords = $totalTeams * ($totalUsers - 1); // 55 * 54 = 2,970
        $expectedTotalRecords = $expectedOwnerRecords + $expectedMemberRecords; // 3,025
        
        $this->command->info("Starting team structure setup with $totalTeams teams and $totalUsers users");
        $this->command->info("Expecting $expectedTotalRecords total team_user records ($expectedOwnerRecords owners + $expectedMemberRecords members)");
        
        // Create one team per role
        foreach ($roles as $role) {
            // Find the owner user for this role (user with matching role)
            $owner = $users->first(function($user) use ($role) {
                return $user->roles->contains('id', $role->id);
            });
            
            if (!$owner) {
                $this->command->warn("No user found with role: {$role->name}");
                continue;
            }
            
            // Check if team already exists for this role
            $team = Team::where('name', Str::plural($role->name))->first();
            
            if (!$team) {
                // Create team if it doesn't exist
                $team = Team::create([
                    '_uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'owner_id' => $owner->id,
                    'name' => Str::plural($role->name),
                    'display_name' => Str::plural($role->name),
                    '_slug' => Str::slug(Str::plural($role->name), '_'),
                    'code' => strtoupper(substr(Str::plural($role->name), 0, 4)),
                    'description' => "Team for {$role->name} role",
                    'type' => 'team',
                    'is_active' => true,
                    'is_verified' => false,
                    '_status' => 1, // Active
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
                
                $this->command->info("Created new team '{$team->name}'");
            } else {
                $this->command->info("Using existing team '{$team->name}'");
            }
            
            // Clear existing team members
            DB::table('team_user')->where('team_id', $team->id)->delete();
            
            // Add owner to the team with full record
            DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $owner->id,
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
                'added_by' => $owner->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Add all other users as members with full record
            $otherUsers = $users->where('id', '!=', $owner->id);
            $memberData = [];
            
            foreach ($otherUsers as $user) {
                $memberData[] = [
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
                    'added_by' => $owner->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // Batch insert all members for this team
            if (!empty($memberData)) {
                DB::table('team_user')->insert($memberData);
            }
            
            $this->command->info("Team '{$team->name}' has 1 owner and " . $otherUsers->count() . " members");
        }
        
        // Verify the counts
        $actualOwnerRecords = DB::table('team_user')->where('role', 'owner')->count();
        $actualMemberRecords = DB::table('team_user')->where('role', 'member')->count();
        $actualTotalRecords = DB::table('team_user')->count();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Output summary
        $this->command->info('\nTeam structure setup completed:');
        $this->command->info("- Teams created: $totalTeams");
        $this->command->info("- Owner records: $actualOwnerRecords (expected: $expectedOwnerRecords)");
        $this->command->info("- Member records: $actualMemberRecords (expected: $expectedMemberRecords)");
        $this->command->info("- Total team_user records: $actualTotalRecords (expected: $expectedTotalRecords)");
        
        if ($actualTotalRecords === $expectedTotalRecords) {
            $this->command->info('✅ Team structure has been set up successfully!');
        } else {
            $this->command->error('❌ There was a mismatch in the expected number of records!');
        }
    }
}
