<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetupTeamMembershipsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear all team memberships
        DB::table('team_user')->truncate();
        
        // Get all teams with role_id
        $teams = Team::whereNotNull('role_id')->get();
        $users = User::all();
        
        foreach ($teams as $team) {
            // Get the role name from the team name (remove the ID part)
            $roleName = trim(preg_replace('/\(\d+\)$/', '', $team->name));
            
            // Find the owner user (email matches role name)
            $ownerEmail = strtolower(str_replace(' ', '.', $roleName)) . '@psilocybin.org';
            $owner = $users->where('email', $ownerEmail)->first();
            
            if ($owner) {
                // Add owner to the team
                $team->users()->sync([$owner->id => ['role' => 'owner']], false);
                
                // Add all other users as members
                $memberData = $users->where('id', '!=', $owner->id)
                    ->mapWithKeys(function($user) {
                        return [$user->id => ['role' => 'member']];
                    })
                    ->toArray();
                
                $team->users()->sync($memberData, false);
                
                $this->command->info("Team '{$team->name}' has been set up with " . 
                    (count($memberData) + 1) . " members (1 owner, " . count($memberData) . " members)");
            } else {
                $this->command->warn("No owner found for team: {$team->name}");
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Team memberships have been set up.');
    }
}
