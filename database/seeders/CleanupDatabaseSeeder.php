<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanupDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting database cleanup...');
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. Remove duplicate teams (keep only the first one for each role)
        $teams = Team::all();
        $uniqueTeams = [];
        
        foreach ($teams as $team) {
            $key = $team->name;
            if (!isset($uniqueTeams[$key])) {
                $uniqueTeams[$key] = $team->id;
            } else {
                // Delete duplicate team
                DB::table('team_user')->where('team_id', $team->id)->delete();
                Team::where('id', $team->id)->delete();
            }
        }

        // 2. Fix team_user table to have exactly 55 owners and 2970 members (total 3025)
        $teams = Team::all();
        $users = User::all();
        
        // Clear all team memberships
        DB::table('team_user')->truncate();
        
        foreach ($teams as $team) {
            // Find the owner (user with matching role)
            $owner = $users->first(function($user) use ($team) {
                return $user->roles->contains('name', Str::singular($team->name));
            });
            
            if (!$owner) continue;
            
            // Add owner with full team_user record
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
            
            // Add all other users as members with full team_user record
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
        
        // 3. Fix role_user table to have exactly 55 records (one per user)
        DB::table('role_user')->truncate();
        
        foreach ($users as $user) {
            // Get the first role for the user (should only be one)
            $role = $user->roles->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // Output summary
        $this->command->info('\nDatabase cleanup completed:');
        $this->command->info('- Teams: ' . Team::count());
        $this->command->info('- Team_User records: ' . DB::table('team_user')->count());
        $this->command->info('- Role_User records: ' . DB::table('role_user')->count());
    }
}
