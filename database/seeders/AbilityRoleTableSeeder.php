<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbilityRoleTableSeeder extends Seeder
{
    /**
     * Assign abilities to roles based on business logic.
     */
    public function run(): void
    {
        // Clear pivot table in a database-agnostic way
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('ability_role')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // For SQLite and other databases
            DB::table('ability_role')->delete();
            // SQLite doesn't use sequences for pivot tables, so no need to reset sequence
        }

        // Cache role & ability IDs for performance
        $roles = collect(DB::table('roles')->get())->mapWithKeys(function ($role) {
            return [$role->name => $role->id];
        });
        
        $abilities = collect(DB::table('abilities')->get())->mapWithKeys(function ($ability) {
            return [$ability->name => $ability->id];
        });

        $assignments = [];

        // ────────────────────── SUPER ADMINISTRATOR ──────────────────────
        $superAdmin = $roles['Super Administrator'];
        foreach ($abilities as $abilityName => $abilityId) {
            $assignments[] = [
                'role_id'     => $superAdmin,
                'ability_id'  => $abilityId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // ────────────────────── GENERAL MANAGER ─────────────────────────
        $generalManager = $roles['General Manager'];
        $gmAbilities = [
            'View Dashboard', 'View Users', 'View Reports', 'Export Data',
            'Manage Reservations', 'Manage Menu', 'Manage Room Status',
            'Manage Events', 'Process Payments', 'Manage Attendance',
            'Log Maintenance', 'Monitor CCTV'
        ];
        foreach ($gmAbilities as $slug) {
            $assignments[] = [
                'role_id'     => $generalManager,
                'ability_id'  => $abilities[$slug],
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // ────────────────────── FINANCE MANAGER ─────────────────────────
        $financeManager = $roles['Finance Manager'];
        $financeAbilities = [
            'View Reports', 'Export Data', 'Process Payments', 'Process Payroll'
        ];
        foreach ($financeAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $financeManager,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── RESTAURANT MANAGER ─────────────────────
        $restaurantManager = $roles['Restaurant Manager'];
        $restaurantAbilities = [
            'Manage Reservations', 'Take Orders', 'Process Payments',
            'Manage Menu', 'View Reports'
        ];
        foreach ($restaurantAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $restaurantManager,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── BAR MANAGER ────────────────────────────
        $barManager = $roles['Bar Manager'];
        $barAbilities = [
            'Serve Drinks', 'Manage Bar Inventory', 'Process Payments', 'View Reports'
        ];
        
        foreach ($barAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $barManager,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── EXECUTIVE CHEF ──────────────────────────
        $executiveChef = $roles['Executive Chef'];
        $chefAbilities = [
            'Prepare Food', 'Manage Recipes', 'Manage Menu', 'View Reports'
        ];
        foreach ($chefAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $executiveChef,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── ACCOMMODATION MANAGER ───────────────────
        $accommodationManager = $roles['Accommodation Manager'];
        $accommodationAbilities = [
            'Check In Guests', 'Check Out Guests', 'Manage Room Status',
            'Manage Reservations', 'Process Payments'
        ];
        foreach ($accommodationAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $accommodationManager,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── FRONT DESK AGENT ────────────────────────
        $frontDesk = $roles['Front Desk Agent'];
        $frontDeskAbilities = [
            'Check In Guests', 'Check Out Guests', 'Manage Reservations'
        ];
        foreach ($frontDeskAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $frontDesk,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── SERVER / WAITER ────────────────────────
        $server = $roles['Server'];
        $serverAbilities = ['Take Orders', 'Process Payments'];
        foreach ($serverAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $server,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── BARTENDER ───────────────────────────────
        $bartender = $roles['Bartender'];
        $bartenderAbilities = ['Serve Drinks'];
        foreach ($bartenderAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $bartender,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── LINE COOK ───────────────────────────────
        $lineCook = $roles['Line Cook'];
        $cookAbilities = ['Prepare Food'];
        foreach ($cookAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $lineCook,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── HOUSEKEEPING ───────────────────────────
        $housekeeping = $roles['Housekeeping'];
        $housekeepingAbilities = ['Manage Room Status'];
        foreach ($housekeepingAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $housekeeping,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── VALET ATTENDANT ────────────────────────
        $valet = $roles['Valet Attendant'];
        $valetAbilities = ['Park Vehicles'];
        foreach ($valetAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $valet,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── STORE KEEPER ───────────────────────────
        $storeKeeper = $roles['Store Keeper'];
        $storeAbilities = ['Receive Stock', 'Issue Stock'];
        foreach ($storeAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $storeKeeper,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── SECURITY GUARD ─────────────────────────
        $security = $roles['Security Guard'];
        $securityAbilities = ['Control Access', 'Monitor CCTV'];
        foreach ($securityAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $security,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // ────────────────────── HR MANAGER ─────────────────────────────
        $hrManager = $roles['HR Manager'];
        $hrAbilities = [
            'Manage Attendance', 'Process Payroll', 'Create Users', 'Edit Users'
        ];
        foreach ($hrAbilities as $abilityName) {
            if (isset($abilities[$abilityName])) {
                $assignments[] = [
                    'role_id'     => $hrManager,
                    'ability_id'  => $abilities[$abilityName],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // Insert in chunks
        foreach (array_chunk($assignments, 50) as $chunk) {
            DB::table('ability_role')->insert($chunk);
        }

        $this->command->info('Successfully assigned abilities to roles!');
    }
}