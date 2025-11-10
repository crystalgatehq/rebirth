<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanupTeamsAndMembershipsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear all team memberships
        DB::table('team_user')->truncate();
        
        // Delete all teams that don't have a matching role (using team name to match role)
        $teams = Team::all();
        $validTeams = [];
        foreach ($teams as $team) {
            $roleName = Str::singular($team->name);
            $role = \App\Models\Role::where('name', $roleName)->first();
            if ($role) {
                $validTeams[] = $team->id;
            } else {
                DB::table('team_user')->where('team_id', $team->id)->delete();
                $team->delete();
            }
        }
        
        // Get all valid teams
        $teams = Team::whereIn('id', $validTeams)->get();
        $users = User::all();
        
        foreach ($teams as $team) {
            $roleName = Str::singular($team->name);
            $role = \App\Models\Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->command->warn("No role found for team: {$team->name}");
                continue;
            }
            
            // Get the owner user for this role (email matches role name)
            $ownerEmail = strtolower(str_replace(' ', '.', $role->name)) . '@psilocybin.org';
            $owner = $users->where('email', $ownerEmail)->first();
            
            if ($owner) {
                // Clear existing team memberships for this team
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
                
                // Insert all members in batch
                if (!empty($memberData)) {
                    DB::table('team_user')->insert($memberData);
                }
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Teams and memberships have been cleaned up.');
    }
}
