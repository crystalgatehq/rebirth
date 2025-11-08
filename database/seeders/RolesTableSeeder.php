<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

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
            // ────────────────────── SYSTEM & EXECUTIVE ──────────────────────
            [
                'name'        => 'Super Administrator',
                'description' => 'Full system access – can do everything.',
                '_hierarchy_matrix_level' => 100,
                '_status'     => Role::ACTIVE
            ],
            [
                'name'        => 'General Manager',
                'description' => 'Oversees the entire business location and all departments.',
                '_hierarchy_matrix_level' => 90,
                '_status'     => Role::ACTIVE
            ],
            [
                'name'        => 'Operations Manager',
                'description' => 'Handles day-to-day operational coordination across all services.',
                '_hierarchy_matrix_level' => 80,
                '_status'     => Role::ACTIVE
            ],
            [
                'name'        => 'Finance Manager',
                'description' => 'Manages accounting, invoicing, payroll and financial reporting.',
                '_hierarchy_matrix_level' => 80,
                '_status'     => Role::ACTIVE
            ],

            // ────────────────────── CLUB / ENTERTAINMENT ───────────────────
            [
                'name'        => 'Club Manager',
                'description' => 'Runs club events, DJ bookings, lighting & sound.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Event Host',
                'description' => 'Manages event stages, lighting and sound.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Waiter',
                'description' => 'Male waiter Serves food and drinks.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Waitress',
                'description' => 'Female waiter Serves food and drinks.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Disk Jockey',
                'description' => 'Performs live music sets in the club.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Performer',
                'description' => 'Performs live music sets in the club.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Bouncer',
                'description' => 'Ensures safety and enforces club entry rules.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── RESTAURANT ─────────────────────────────
            [
                'name'        => 'Restaurant Manager',
                'description' => 'Supervises dining room, staff and customer experience.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Maître d’Hôtel',
                'description' => 'Manages reservations, seating and front-of-house flow.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Host/Hostess',
                'description' => 'Greets guests, handles bookings and seats tables.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Server',
                'description' => 'Takes orders, serves food & drinks.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Food Runner',
                'description' => 'Delivers dishes from kitchen to tables.',
                '_hierarchy_matrix_level' => 30,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Busser',
                'description' => 'Clears tables, resets settings, assists servers.',
                '_hierarchy_matrix_level' => 30,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── BAR ───────────────────────────────────
            [
                'name'        => 'Bar Manager',
                'description' => 'Oversees bar inventory, staff and beverage program.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Head Bartender',
                'description' => 'Creates cocktails, trains bartenders.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Bartender',
                'description' => 'Prepares and serves drinks.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Barback',
                'description' => 'Restocks bar, cleans glassware, assists bartenders.',
                '_hierarchy_matrix_level' => 30,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Sommelier',
                'description' => 'Curates wine list, advises guests, manages cellar.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── KITCHEN ───────────────────────────────
            [
                'name'        => 'Executive Chef',
                'description' => 'Designs menus, oversees all culinary operations.',
                '_hierarchy_matrix_level' => 80,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Head Chef',
                'description' => 'Runs the kitchen brigade on a daily basis.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Sous Chef',
                'description' => 'Second-in-command, assists head chef.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Pastry Chef',
                'description' => 'Creates desserts, breads and pastries.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Line Cook',
                'description' => 'Works a specific station (grill, sauté, etc.).',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Prep Cook',
                'description' => 'Chops, marinates, prepares mise en place.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── ACCOMMODATION / ROOMS ──────────────────
            [
                'name'        => 'Accommodation Manager',
                'description' => 'Supervises rooms, housekeeping and guest services.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Front Desk Agent',
                'description' => 'Handles check-in/out, guest inquiries.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Housekeeping',
                'description' => 'Cleans guest rooms and public areas.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Concierge',
                'description' => 'Arranges tours, transport, special requests.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── TOURS & TRANSPORT ─────────────────────
            [
                'name'        => 'Tour Manager',
                'description' => 'Plans and executes off-site tours and excursions.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Tour Guide',
                'description' => 'Leads guests on tours, provides commentary.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Driver',
                'description' => 'Transports guests to/from destinations.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── CAR WASH & VALET ──────────────────────
            [
                'name'        => 'Car Wash Manager',
                'description' => 'Runs car-wash and detailing services.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Car Detailer',
                'description' => 'Performs interior/exterior detailing.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Car Wash Attendant',
                'description' => 'Washes vehicles, dries and vacuums.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Valet Attendant',
                'description' => 'Parks and retrieves guest vehicles.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── PARKING ───────────────────────────────
            [
                'name'        => 'Parking Manager',
                'description' => 'Supervises parking lot operations.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Parking Attendant',
                'description' => 'Directs cars, collects fees, assists guests.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── INVENTORY & PROCUREMENT ───────────────
            [
                'name'        => 'Inventory Manager',
                'description' => 'Tracks stock levels for food, beverage, supplies.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Store Keeper',
                'description' => 'Issues and receives goods in the storeroom.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── HR ───────────────────────────────────
            [
                'name'        => 'HR Manager',
                'description' => 'Handles recruitment, payroll, employee relations.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Recruiter',
                'description' => 'Sources and on-boards new staff.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── SALES & MARKETING ────────────────────
            [
                'name'        => 'Sales & Marketing Manager',
                'description' => 'Drives promotions, partnerships and revenue growth.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Sales Person',
                'description' => 'Sells products and services.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Social Media Influencer',
                'description' => 'Promotes products and services.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── MAINTENANCE & FACILITIES ──────────────
            [
                'name'        => 'Maintenance Manager',
                'description' => 'Oversees building repairs, plumbing, electrical.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'Maintenance Technician',
                'description' => 'Performs repairs and preventive maintenance.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── CUSTOMER CARE & IT TECHNICIAN SUPPORT ──────────────────────
            [
                'name'        => 'Customer Care Support',
                'description' => 'Resolves customer care issues for staff.',
                '_hierarchy_matrix_level' => 50,
                '_status'     => Role::SUSPENDED
            ],
            [
                'name'        => 'IT Technician Support',
                'description' => 'Resolves IT technician issues for staff.',
                '_hierarchy_matrix_level' => 60,
                '_status'     => Role::SUSPENDED
            ],

            // ────────────────────── SECURITY & CCTV ───────────────────────
            [
                'name'        => 'Security Manager',
                'description' => 'Coordinates all security personnel and protocols.',
                '_hierarchy_matrix_level' => 70,
                '_status'     => Role::ACTIVE
            ],
            [
                'name'        => 'Security Guard',
                'description' => 'Monitors security.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::ACTIVE
            ],
            [
                'name'        => 'CCTV Operator',
                'description' => 'Monitors surveillance feeds and logs incidents.',
                '_hierarchy_matrix_level' => 40,
                '_status'     => Role::SUSPENDED
            ],
        ];

        // Insert in chunks for better performance with large datasets
        foreach (array_chunk($roles, 20) as $chunk) {
            // Add slugs to each role in the chunk
            $chunk = array_map(function($role) {
                $role['_slug'] = \Illuminate\Support\Str::slug($role['name']);
                return $role;
            }, $chunk);
            DB::table('roles')->insert($chunk);
        }

        $this->command->info('Successfully seeded ' . count($roles) . ' roles!');
    }
}