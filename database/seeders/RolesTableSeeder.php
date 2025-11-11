<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing roles in a database-agnostic way
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('roles')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // For SQLite and other databases
            DB::table('roles')->delete();
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement('DELETE FROM sqlite_sequence WHERE name = "roles"');
            }
        }

        $roles = [
            // System & Executive
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Full system access – can do everything.',
                '_hierarchy_matrix_level' => 100,
                'status' => 1
            ],
            [
                'name' => 'General Manager',
                'slug' => 'general-manager',
                'description' => 'Oversees the entire business location and all departments.',
                '_hierarchy_matrix_level' => 90,
                'status' => 1
            ],
            [
                'name' => 'Operations Manager',
                'slug' => 'operations-manager',
                'description' => 'Manages daily operations and staff.',
                '_hierarchy_matrix_level' => 80,
                'status' => 1
            ],
            
            // Support & Security
            [
                'name' => 'Support Agent',
                'slug' => 'support-agent',
                'description' => 'Provides customer support and assistance.',
                '_hierarchy_matrix_level' => 30,
                'status' => 1
            ],
            [
                'name' => 'Security Personnel',
                'slug' => 'security-personnel',
                'description' => 'Ensures safety and security on premises.',
                '_hierarchy_matrix_level' => 30,
                'status' => 1
            ],
            
            // Content & Moderation
            [
                'name' => 'Content Moderator',
                'slug' => 'content-moderator',
                'description' => 'Moderates user-generated content.',
                '_hierarchy_matrix_level' => 50,
                'status' => 1
            ],
            
            // Standard User Roles
            [
                'name' => 'Registered User',
                'slug' => 'registered-user',
                'description' => 'Standard user with basic permissions.',
                '_hierarchy_matrix_level' => 20,
                'status' => 1
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Limited access, read-only.',
                '_hierarchy_matrix_level' => 10,
                'status' => 1
            ]
        ];

        // Insert roles one by one
        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                '_hierarchy_matrix_level' => $role['_hierarchy_matrix_level'],
                'status' => $role['status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->info('Successfully seeded ' . count($roles) . ' roles!');
    }
}
