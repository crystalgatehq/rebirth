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
        
        // Delete all teams that don't have a role_id set
        Team::whereNull('role_id')->delete();
        
        // Get all teams with roles
        $teams = Team::whereNotNull('role_id')->get();
        $users = User::all();
        
        foreach ($teams as $team) {
            $role = $team->role;
            if (!$role) {
                $this->command->warn("No role found for team: {$team->name}");
                continue;
            }
            
            // Get the owner user for this role (email matches role name)
            $ownerEmail = strtolower(str_replace(' ', '.', $role->name)) . '@psilocybin.org';
            $owner = $users->where('email', $ownerEmail)->first();
            
            if ($owner) {
                // Add owner to the team
                $team->users()->sync([$owner->id => ['role' => 'owner']], false);
                
                // Add all other users as members
                $memberIds = $users->where('id', '!=', $owner->id)
                    ->pluck('id')
                    ->mapWithKeys(fn($id) => [$id => ['role' => 'member']]);
                
                $team->users()->sync($memberIds, false);
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Teams and memberships have been cleaned up.');
    }
}
