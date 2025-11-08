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
            $team = Team::where('role_id', $role->id)->first();
            
            if (!$team) {
                // Create team if it doesn't exist
                $team = Team::create([
                    'name' => Str::plural($role->name),
                    '_uuid' => (string) \Illuminate\Support\Str::uuid(),
                    '_slug' => Str::slug(Str::plural($role->name), '_'),
                    'description' => "Team for {$role->name} role",
                    'user_id' => $owner->id,
                    'personal_team' => false,
                    '_status' => 1, // Active
                    'role_id' => $role->id,
                ]);
                
                $this->command->info("Created new team '{$team->name}'");
            } else {
                $this->command->info("Using existing team '{$team->name}'");
            }
            
            // Clear existing team members
            DB::table('team_user')->where('team_id', $team->id)->delete();
            
            // Add owner to the team
            DB::table('team_user')->updateOrInsert(
                ['team_id' => $team->id, 'user_id' => $owner->id],
                ['role' => 'owner', 'created_at' => now(), 'updated_at' => now()]
            );
            
            // Add all other users as members in a single query for better performance
            $otherUsers = $users->where('id', '!=', $owner->id)->pluck('id');
            $memberData = $otherUsers->map(function($userId) use ($team) {
                return [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();
            
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
