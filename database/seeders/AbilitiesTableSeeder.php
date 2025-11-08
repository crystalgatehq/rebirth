<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Ability;

class AbilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing abilities in a database-agnostic way
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('abilities')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } else {
            // For SQLite and other databases
            DB::table('abilities')->delete();
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement('DELETE FROM sqlite_sequence WHERE name = "abilities"');
            }
        }

        $abilities = [
            // ────────────────────── SYSTEM & CORE ──────────────────────
            [
                'name'        => 'Manage System',
                'description' => 'Full access to all system settings, users, roles, and abilities.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'View Dashboard',
                'description' => 'Access to the main analytics dashboard.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── USER MANAGEMENT ───────────────────
            [
                'name'        => 'Create Users',
                'description' => 'Add new staff or customer accounts.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Edit Users',
                'description' => 'Modify user profiles, roles, and permissions.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Delete Users',
                'description' => 'Permanently remove user accounts.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'View Users',
                'description' => 'See list of all users and their details.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── ROLE & ABILITY MANAGEMENT ─────────
            [
                'name'        => 'Manage Roles',
                'description' => 'Create, edit, and assign roles.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Abilities',
                'description' => 'Create, edit, and assign permissions (abilities).',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── RESTAURANT OPERATIONS ─────────────
            [
                'name'        => 'Manage Reservations',
                'description' => 'Create, update, and cancel table bookings.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Take Orders',
                'description' => 'Record customer food and drink orders.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Process Payments',
                'description' => 'Handle cash, card, and mobile payments.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Menu',
                'description' => 'Add, edit, or remove menu items and prices.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── BAR OPERATIONS ────────────────────
            [
                'name'        => 'Serve Drinks',
                'description' => 'Prepare and serve alcoholic and non-alcoholic beverages.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Bar Inventory',
                'description' => 'Track liquor, mixers, and bar supplies.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── KITCHEN OPERATIONS ────────────────
            [
                'name'        => 'Prepare Food',
                'description' => 'Cook and plate dishes according to recipes.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Recipes',
                'description' => 'Create and update standard operating recipes.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── ACCOMMODATION ─────────────────────
            [
                'name'        => 'Check In Guests',
                'description' => 'Register arriving guests and assign rooms.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Check Out Guests',
                'description' => 'Process departure and finalize bills.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Room Status',
                'description' => 'Mark rooms as clean, dirty, occupied, or maintenance.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── TOURS & TRANSPORT ─────────────────
            [
                'name'        => 'Book Tours',
                'description' => 'Schedule and confirm guest excursions.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Drive Guests',
                'description' => 'Operate shuttle or tour vehicles safely.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── CAR WASH & VALET ──────────────────
            [
                'name'        => 'Wash Vehicles',
                'description' => 'Clean guest and staff vehicles.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Park Vehicles',
                'description' => 'Valet park and retrieve guest cars.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── INVENTORY & PROCUREMENT ───────────
            [
                'name'        => 'Receive Stock',
                'description' => 'Accept and verify incoming supplies.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Issue Stock',
                'description' => 'Distribute items from storage to departments.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Adjust Inventory',
                'description' => 'Correct stock levels due to damage, loss, or audit.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── FINANCE & REPORTING ───────────────
            [
                'name'        => 'View Reports',
                'description' => 'Access sales, expense, and performance reports.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Export Data',
                'description' => 'Download data in CSV, PDF, or Excel.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── HR & PAYROLL ──────────────────────
            [
                'name'        => 'Manage Attendance',
                'description' => 'Clock in/out staff and track working hours.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Process Payroll',
                'description' => 'Calculate and distribute employee salaries.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── MAINTENANCE ───────────────────────
            [
                'name'        => 'Log Maintenance',
                'description' => 'Report and track repair requests.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Perform Repairs',
                'description' => 'Fix plumbing, electrical, or structural issues.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── SECURITY ──────────────────────────
            [
                'name'        => 'Monitor CCTV',
                'description' => 'Watch live feeds and review footage.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Control Access',
                'description' => 'Grant or deny entry to restricted areas.',
                '_status'     => Ability::ACTIVE
            ],

            // ────────────────────── MARKETING & SALES ─────────────────
            [
                'name'        => 'Send Promotions',
                'description' => 'Email or SMS marketing campaigns to guests.',
                '_status'     => Ability::ACTIVE
            ],
            [
                'name'        => 'Manage Events',
                'description' => 'Plan and promote club nights, dinners, or tours.',
                '_status'     => Ability::ACTIVE
            ],
        ];

        // Insert in chunks
        foreach (array_chunk($abilities, 20) as $chunk) {
            // Add slugs to each ability in the chunk
            $chunk = array_map(function($ability) {
                $ability['_slug'] = \Illuminate\Support\Str::slug($ability['name']);
                return $ability;
            }, $chunk);
            DB::table('abilities')->insert($chunk);
        }

        $this->command->info('Successfully seeded ' . count($abilities) . ' abilities!');
    }
}