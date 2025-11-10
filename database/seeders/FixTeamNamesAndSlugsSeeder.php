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
            // Remove the role ID from the team name if it exists
            $cleanName = trim(preg_replace('/\(\d+\)$/', '', $team->name));
            
            // Generate a clean slug with underscores
            $slug = str_replace('-', '_', Str::slug($cleanName));
            
            // Update the team with all required fields
            $team->update([
                'name' => $cleanName,
                'display_name' => $cleanName,
                '_slug' => $slug,
                'code' => strtoupper(substr($cleanName, 0, 4)),
                'description' => $team->description ?? "Team for {$cleanName} role",
                'type' => $team->type ?? 'team',
                'is_active' => $team->is_active ?? true,
                'is_verified' => $team->is_verified ?? false,
                '_status' => $team->_status ?? 1, // Active
                'settings' => $team->settings ?? json_encode([
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
                'communication_settings' => $team->communication_settings ?? json_encode([
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
        }
        
        $this->command->info('Team names and slugs have been fixed.');
    }
}
