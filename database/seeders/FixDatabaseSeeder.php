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
        
        // Clear the table
        DB::table('role_user')->truncate();
        
        // Get all users and assign each to one role based on email
        $users = User::all();
        
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
                // Assign the role to the user
                DB::table('role_user')->insert([
                    'role_id' => $role->id,
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->command->info("Assigned role '{$role->name}' to user '{$user->email}'");
            } else {
                $this->command->warn("No matching role found for user: {$user->email} (tried: {$roleName})");
            }
        }
        
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
                ['role_id' => $role->id],
                [
                    'name' => Str::plural($role->name),
                    '_uuid' => (string) Str::uuid(),
                    '_slug' => Str::slug(Str::plural($role->name), '_'),
                    'description' => "Team for {$role->name} role",
                    'user_id' => 1, // Default to first user as owner, will be updated later
                    'personal_team' => false,
                    '_status' => 1, // Active
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
                return $user->roles->contains('id', $team->role_id);
            });
            
            if (!$owner) {
                $this->command->warn("No owner found for team '{$team->name}'. Using first user as owner.");
                $owner = $users->first();
            }
            
            // Update team owner if needed
            if ($team->user_id !== $owner->id) {
                $team->user_id = $owner->id;
                $team->save();
            }
            
            // Add owner to the team
            DB::table('team_user')->insert([
                'team_id' => $team->id,
                'user_id' => $owner->id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Add all other users as members
            $otherUsers = $users->where('id', '!=', $owner->id);
            $memberData = [];
            
            foreach ($otherUsers as $user) {
                $memberData[] = [
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now()
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
