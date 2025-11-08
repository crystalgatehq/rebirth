<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FixTeamNamesAndSlugsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        
        foreach ($teams as $team) {
            // Remove the role ID from the team name
            $cleanName = trim(preg_replace('/\(\d+\)$/', '', $team->name));
            
            // Generate a clean slug with underscores
            $slug = str_replace('-', '_', Str::slug($cleanName));
            
            // Update the team
            $team->update([
                'name' => $cleanName,
                '_slug' => $slug,
            ]);
        }
        
        $this->command->info('Team names and slugs have been fixed.');
    }
}
