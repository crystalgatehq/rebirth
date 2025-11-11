<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use App\Models\TeamUser;
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
            $teamName = Str::plural($role->name);
            $abbreviatedName = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $teamName), 0, 4));
            
            // Get or create team for this role
            $team = Team::firstOrCreate(
                ['name' => $teamName],
                [
                    'uuid' => (string) Str::uuid(),
                    'owner_id' => null, // Will be updated later
                    'display_name' => $teamName,
                    'abbrivated_name' => $abbreviatedName,
                    'slug' => Str::slug($teamName),
                    'description' => "Team for {$role->name} role",
                    'personal_team' => false,
                    'status' => Team::STATUS_ACTIVE,
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
        $this->command->info("teams table now has {$count} records");
    }
    
    protected function fixTeamUserTable()
    {
        $this->command->info('Fixing team_user table...');
        
        // Clear the table
        DB::table('team_user')->truncate();
        // Get all teams and users with their roles loaded
        $teams = Team::all();
        $users = User::with('roles')->get();
        
        $assignments = [];
        $now = now();
        
        // For each team, assign the owner and members
        foreach ($teams as $team) {
            // Try to find a user with a matching role name
            $owner = $users->first(function($user) use ($team) {
                $singularTeamName = Str::singular($team->name);
                return $user->roles->contains('name', $singularTeamName);
            });
            
            // If no matching role, use the first user as owner
            if (!$owner) {
                $owner = $users->first();
                $this->command->warn("No user with matching role found for team '{$team->name}'. Using '{$owner->email}' as owner.");
            }
            
            if ($owner) {
                // Update team owner
                $team->owner_id = $owner->id;
                $team->save();
                
                // Add owner to team_user table
                $assignments[] = [
                    'team_id' => $team->id,
                    'user_id' => $owner->id,
                    'play' => 'owner',
                    'status' => TeamUser::STATUS_ACTIVE,
                    'constraints' => null,
                    'activities' => null,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                
                $this->command->info("Assigned owner '{$owner->email}' to team '{$team->name}'");
                
                // Add 1-3 random members to each team (excluding the owner)
                $potentialMembers = $users->where('id', '!=', $owner->id)->random(min(3, $users->count() - 1));
                
                foreach ($potentialMembers as $member) {
                    $assignments[] = [
                        'team_id' => $team->id,
                        'user_id' => $member->id,
                        'play' => 'member',
                        'status' => TeamUser::STATUS_ACTIVE,
                        'constraints' => null,
                        'activities' => null,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    
                    $this->command->info("Added member '{$member->email}' to team '{$team->name}'", 'v');
                }
            }
        }
        
        // Insert all assignments in chunks for better performance
        foreach (array_chunk($assignments, 100) as $chunk) {
            DB::table('team_user')->insert($chunk);
        }
        
        // Verify counts
        $ownerCount = DB::table('team_user')->where('play', 'owner')->count();
        $memberCount = DB::table('team_user')->where('play', 'member')->count();
        $totalCount = $ownerCount + $memberCount;
        
        $this->command->info("team_user table now has {$totalCount} records");
        $this->command->info("- Owners: {$ownerCount}");
        $this->command->info("- Members: {$memberCount}");
    }
}
