<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'View Dashboard',
                'description' => 'Access to the main analytics dashboard.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── USER MANAGEMENT ───────────────────
            [
                'name'        => 'Create Users',
                'description' => 'Add new staff or customer accounts.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Edit Users',
                'description' => 'Modify user profiles, roles, and permissions.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Delete Users',
                'description' => 'Permanently remove user accounts.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'View Users',
                'description' => 'See list of all users and their details.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── ROLE & ABILITY MANAGEMENT ─────────
            [
                'name'        => 'Manage Roles',
                'description' => 'Create, edit, and assign roles.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Manage Abilities',
                'description' => 'Create, edit, and assign permissions (abilities).',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── PAYMENT PROCESSING ───────────────
            [
                'name'        => 'Process Payments',
                'description' => 'Handle cash, card, and mobile payments.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Check Out Guests',
                'description' => 'Process departure and finalize bills.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Manage Room Status',
                'description' => 'Mark rooms as clean, dirty, occupied, or maintenance.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── TOURS & TRANSPORT ─────────────────
            [
                'name'        => 'Book Tours',
                'description' => 'Schedule and confirm guest excursions.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Drive Guests',
                'description' => 'Operate shuttle or tour vehicles safely.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── CAR WASH & VALET ──────────────────
            [
                'name'        => 'Wash Vehicles',
                'description' => 'Clean guest and staff vehicles.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Park Vehicles',
                'description' => 'Valet park and retrieve guest cars.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── INVENTORY & PROCUREMENT ───────────
            [
                'name'        => 'Receive Stock',
                'description' => 'Accept and verify incoming supplies.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Issue Stock',
                'description' => 'Distribute items from storage to departments.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Adjust Inventory',
                'description' => 'Correct stock levels due to damage, loss, or audit.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── FINANCE & REPORTING ───────────────
            [
                'name'        => 'View Reports',
                'description' => 'Access sales, expense, and performance reports.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Export Data',
                'description' => 'Download data in CSV, PDF, or Excel.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── HR & PAYROLL ──────────────────────
            [
                'name'        => 'Manage Attendance',
                'description' => 'Clock in/out staff and track working hours.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Process Payroll',
                'description' => 'Calculate and distribute employee salaries.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── MAINTENANCE ───────────────────────
            [
                'name'        => 'Log Maintenance',
                'description' => 'Report and track repair requests.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Perform Repairs',
                'description' => 'Fix plumbing, electrical, or structural issues.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── SECURITY ──────────────────────────
            [
                'name'        => 'Monitor CCTV',
                'description' => 'Watch live feeds and review footage.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Control Access',
                'description' => 'Grant or deny entry to restricted areas.',
                'status'     => Ability::STATUS_ACTIVE
            ],

            // ────────────────────── MARKETING & SALES ─────────────────
            [
                'name'        => 'Send Promotions',
                'description' => 'Email or SMS marketing campaigns to guests.',
                'status'     => Ability::STATUS_ACTIVE
            ],
            [
                'name'        => 'Manage Events',
                'description' => 'Create and manage special events and promotions.',
                'status'     => Ability::STATUS_ACTIVE
            ]
        ];

        // Insert abilities one by one to handle UUIDs properly
        foreach ($abilities as $ability) {
            DB::table('abilities')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => $ability['name'],
                'slug' => Str::slug(str_replace(' ', '-', $ability['name'])),
                'description' => $ability['description'] ?? null,
                'status' => $ability['status'] ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->info('Successfully seeded ' . count($abilities) . ' abilities!');
    }
}