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
            $key = $team->role_id ?? $team->name;
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
                return $user->roles->contains('id', $team->role_id);
            });
            
            if (!$owner) continue;
            
            // Add owner
            $team->users()->attach($owner->id, ['role' => 'owner']);
            
            // Add all other users as members
            $otherUsers = $users->where('id', '!=', $owner->id);
            $memberData = [];
            
            foreach ($otherUsers as $user) {
                $memberData[$user->id] = ['role' => 'member'];
            }
            
            $team->users()->attach($memberData);
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
