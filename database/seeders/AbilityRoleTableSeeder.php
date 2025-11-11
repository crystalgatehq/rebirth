<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbilityRoleTableSeeder extends Seeder
{
    /**
     * Role to abilities mapping
     * 
     * @var array
     */
    protected $roleAbilities = [
        'Administrator' => '*', // All abilities
        'General Manager' => [
            'View Dashboard', 'View Users', 'View Reports', 'Export Data',
            'Manage Room Status', 'Process Payments', 'Monitor CCTV'
        ],
        'Finance Manager' => [
            'View Reports', 'Export Data', 'Process Payments', 'Process Payroll'
        ],
        'Operations Manager' => [
            'View Dashboard', 'View Reports', 'Manage Room Status', 'Process Payments'
        ],
        'Support Agent' => [
            'View Dashboard', 'View Users', 'View Reports'
        ],
        'Security Personnel' => [
            'Monitor CCTV', 'Control Access'
        ],
        'Content Moderator' => [
            'View Reports', 'Manage Content'
        ],
        'Registered User' => [
            'View Dashboard'
        ]
    ];

    /**
     * Assign abilities to roles based on business logic.
     */
    public function run(): void
    {
        // Clear existing role-ability relationships
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ability_role')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            DB::table('ability_role')->delete();
        }

        // Cache roles and abilities for better performance
        $roles = collect(DB::table('roles')->get())->keyBy('name');
        $abilities = collect(DB::table('abilities')->get())->keyBy('name');
        
        $assignments = [];
        $now = now();

        foreach ($this->roleAbilities as $roleName => $roleAbilities) {
            if (!isset($roles[$roleName])) {
                $this->command->warn("Role '{$roleName}' not found. Skipping...");
                continue;
            }

            $roleId = $roles[$roleName]->id;

            // Handle administrator role (gets all abilities)
            if ($roleAbilities === '*') {
                foreach ($abilities as $ability) {
                    $assignments[] = [
                        'role_id' => $roleId,
                        'ability_id' => $ability->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                continue;
            }

            // Process regular role abilities
            foreach ($roleAbilities as $abilityName) {
                if (!isset($abilities[$abilityName])) {
                    $this->command->warn("Ability '{$abilityName}' not found for role '{$roleName}'. Skipping...");
                    continue;
                }

                $assignments[] = [
                    'role_id' => $roleId,
                    'ability_id' => $abilities[$abilityName]->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insert assignments in chunks for better performance
        foreach (array_chunk($assignments, 100) as $chunk) {
            DB::table('ability_role')->insert($chunk);
        }

        $this->command->info('Successfully assigned abilities to roles!');
    }
}