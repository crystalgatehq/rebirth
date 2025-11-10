<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
// Remove model import to prevent dependency during seeding

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
                'name' => 'Administrator',
                '_slug' => 'administrator',
                'description' => 'Full system access – can do everything.',
                'type' => 'system',
                'level' => 100,
                'settings' => json_encode(['is_system' => true]),
                'communication_limits' => json_encode(['unlimited' => true]),
                '_status' => 1
            ],
            [
                'name' => 'General Manager',
                '_slug' => 'general-manager',
                'description' => 'Oversees the entire business location and all departments.',
                'type' => 'management',
                'level' => 90,
                'settings' => json_encode(['is_system' => true]),
                'communication_limits' => json_encode(['daily_limit' => 1000]),
                '_status' => 1
            ],
            [
                'name' => 'Operations Manager',
                '_slug' => 'operations-manager',
                'description' => 'Handles day-to-day operational coordination across all services.',
                'type' => 'management',
                'level' => 80,
                'settings' => json_encode(['is_system' => true]),
                'communication_limits' => json_encode(['daily_limit' => 500]),
                '_status' => 1
            ],
            [
                'name' => 'Finance Manager',
                '_slug' => 'finance-manager',
                'description' => 'Manages accounting, invoicing, payroll and financial reporting.',
                'type' => 'management',
                'level' => 80,
                'settings' => json_encode(['is_system' => true]),
                'communication_limits' => json_encode(['daily_limit' => 500]),
                '_status' => 1
            ],
            
            // ────────────────────── CLUB / ENTERTAINMENT ───────────────────
            [
                'name' => 'Club Manager',
                '_slug' => 'club-manager',
                'description' => 'Runs club events, DJ bookings, lighting & sound.',
                'type' => 'entertainment',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Event Host',
                '_slug' => 'event-host',
                'description' => 'Manages event stages, lighting and sound.',
                'type' => 'entertainment',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Waiter',
                '_slug' => 'waiter',
                'description' => 'Male waiter Serves food and drinks.',
                'type' => 'restaurant',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Waitress',
                '_slug' => 'waitress',
                'description' => 'Female waiter Serves food and drinks.',
                'type' => 'restaurant',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Disk Jockey',
                '_slug' => 'disk-jockey',
                'description' => 'Performs live music sets in the club.',
                'type' => 'entertainment',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Performer',
                '_slug' => 'performer',
                'description' => 'Performs live music sets in the club.',
                'type' => 'entertainment',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Bouncer',
                '_slug' => 'bouncer',
                'description' => 'Ensures safety and enforces club entry rules.',
                'type' => 'security',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],

            // ────────────────────── RESTAURANT ─────────────────────────────
            [
                'name' => 'Restaurant Manager',
                '_slug' => 'restaurant-manager',
                'description' => 'Supervises dining room, staff and customer experience.',
                'type' => 'restaurant',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Maître d’Hôtel',
                '_slug' => 'maitre-dhotel',
                'description' => 'Manages reservations, seating and front-of-house flow.',
                'type' => 'restaurant',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Host/Hostess',
                '_slug' => 'host-hostess',
                'description' => 'Greets guests, handles bookings and seats tables.',
                'type' => 'restaurant',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Server',
                '_slug' => 'server',
                'description' => 'Takes orders, serves food & drinks.',
                'type' => 'restaurant',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Food Runner',
                '_slug' => 'food-runner',
                'description' => 'Delivers dishes from kitchen to tables.',
                'type' => 'restaurant',
                'level' => 30,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],
            [
                'name' => 'Busser',
                '_slug' => 'busser',
                'description' => 'Clears tables, resets settings, assists servers.',
                'type' => 'restaurant',
                'level' => 30,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],

            // ────────────────────── BAR ───────────────────────────────────
            [
                'name' => 'Bar Manager',
                '_slug' => 'bar-manager',
                'description' => 'Oversees bar inventory, staff and beverage program.',
                'type' => 'bar',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Head Bartender',
                '_slug' => 'head-bartender',
                'description' => 'Leads bar team, creates cocktails, manages service.',
                'type' => 'bar',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 150]),
                '_status' => 1
            ],
            [
                'name' => 'Bartender',
                '_slug' => 'bartender',
                'description' => 'Mixes and serves drinks, processes payments.',
                'type' => 'bar',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Barback',
                '_slug' => 'barback',
                'description' => 'Restocks bar, preps garnishes, assists bartenders.',
                'type' => 'bar',
                'level' => 30,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],
            [
                'name' => 'Sommelier',
                '_slug' => 'sommelier',
                'description' => 'Curates wine list, advises guests, manages cellar.',
                'type' => 'bar',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],

            // ────────────────────── KITCHEN ───────────────────────────────
            [
                'name' => 'Executive Chef',
                '_slug' => 'executive-chef',
                'description' => 'Designs menus, oversees all culinary operations.',
                'type' => 'kitchen',
                'level' => 80,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Head Chef',
                '_slug' => 'head-chef',
                'description' => 'Runs the kitchen brigade on a daily basis.',
                'type' => 'kitchen',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 80]),
                '_status' => 1
            ],
            [
                'name' => 'Sous Chef',
                '_slug' => 'sous-chef',
                'description' => 'Second-in-command, manages kitchen staff.',
                'type' => 'kitchen',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 80]),
                '_status' => 1
            ],
            [
                'name' => 'Line Cook',
                '_slug' => 'line-cook',
                'description' => 'Prepares food items, follows recipes.',
                'type' => 'kitchen',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Prep Cook',
                '_slug' => 'prep-cook',
                'description' => 'Preps ingredients, assists with basic cooking.',
                'type' => 'kitchen',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],
            [
                'name' => 'Pastry Chef',
                '_slug' => 'pastry-chef',
                'description' => 'Creates desserts, pastries and baked goods.',
                'type' => 'kitchen',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Dishwasher',
                '_slug' => 'dishwasher',
                'description' => 'Cleans dishes, maintains kitchen hygiene.',
                'type' => 'kitchen',
                'level' => 20,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 10]),
                '_status' => 1
            ],

            // ────────────────────── ACCOMMODATION / ROOMS ──────────────────
            [
                'name' => 'Accommodation Manager',
                '_slug' => 'accommodation-manager',
                'description' => 'Supervises rooms, housekeeping and guest services.',
                'type' => 'accommodation',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Front Desk Agent',
                '_slug' => 'front-desk-agent',
                'description' => 'Handles check-in/out, guest inquiries.',
                'type' => 'accommodation',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Housekeeping',
                '_slug' => 'housekeeping',
                'description' => 'Cleans guest rooms and public areas.',
                'type' => 'accommodation',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Concierge',
                '_slug' => 'concierge',
                'description' => 'Arranges tours, transport, special requests.',
                'type' => 'accommodation',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],

            // ────────────────────── TOURS & TRANSPORT ─────────────────────
            [
                'name' => 'Tour Manager',
                '_slug' => 'tour-manager',
                'description' => 'Plans and executes off-site tours and excursions.',
                'type' => 'tours',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 150]),
                '_status' => 1
            ],
            [
                'name' => 'Tour Guide',
                '_slug' => 'tour-guide',
                'description' => 'Leads guests on tours, provides commentary.',
                'type' => 'tours',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Driver',
                '_slug' => 'driver',
                'description' => 'Transports guests to/from destinations.',
                'type' => 'transport',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],

            // ────────────────────── CAR WASH & VALET ──────────────────────
            [
                'name' => 'Car Wash Manager',
                '_slug' => 'car-wash-manager',
                'description' => 'Runs car-wash and detailing services.',
                'type' => 'valet',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Car Detailer',
                '_slug' => 'car-detailer',
                'description' => 'Performs interior/exterior detailing.',
                'type' => 'valet',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
            [
                'name' => 'Car Wash Attendant',
                '_slug' => 'car-wash-attendant',
                'description' => 'Washes vehicles, dries and vacuums.',
                'type' => 'valet',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],
            [
                'name' => 'Valet Attendant',
                '_slug' => 'valet-attendant',
                'description' => 'Parks and retrieves guest vehicles.',
                'type' => 'valet',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 30]),
                '_status' => 1
            ],

            // ────────────────────── PARKING ───────────────────────────────
            [
                'name' => 'Parking Manager',
                '_slug' => 'parking-manager',
                'description' => 'Supervises parking lot operations.',
                'type' => 'parking',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Parking Attendant',
                '_slug' => 'parking-attendant',
                'description' => 'Directs cars, collects fees, assists guests.',
                'type' => 'parking',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],

            // ────────────────────── INVENTORY & PROCUREMENT ───────────────
            [
                'name' => 'Inventory Manager',
                '_slug' => 'inventory-manager',
                'description' => 'Tracks stock levels for food, beverage, supplies.',
                'type' => 'inventory',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Store Keeper',
                '_slug' => 'store-keeper',
                'description' => 'Issues and receives goods in the storeroom.',
                'type' => 'inventory',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],

            // ────────────────────── HR ───────────────────────────────────
            [
                'name' => 'HR Manager',
                '_slug' => 'hr-manager',
                'description' => 'Handles recruitment, payroll, employee relations.',
                'type' => 'hr',
                'level' => 70,
                'settings' => json_encode(['is_system' => true]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Recruiter',
                '_slug' => 'recruiter',
                'description' => 'Sources and on-boards new staff.',
                'type' => 'hr',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],

            // ────────────────────── SALES & MARKETING ────────────────────
            [
                'name' => 'Sales & Marketing Manager',
                '_slug' => 'sales-marketing-manager',
                'description' => 'Drives promotions, partnerships and revenue growth.',
                'type' => 'sales',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 200]),
                '_status' => 1
            ],
            [
                'name' => 'Sales Person',
                '_slug' => 'sales-person',
                'description' => 'Sells products and services.',
                'type' => 'sales',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Social Media Influencer',
                '_slug' => 'social-media-influencer',
                'description' => 'Promotes products and services.',
                'type' => 'marketing',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],

            // ────────────────────── MAINTENANCE & FACILITIES ──────────────
            [
                'name' => 'Maintenance Manager',
                '_slug' => 'maintenance-manager',
                'description' => 'Oversees building repairs, plumbing, electrical.',
                'type' => 'maintenance',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Maintenance Technician',
                '_slug' => 'maintenance-technician',
                'description' => 'Performs repairs and preventive maintenance.',
                'type' => 'maintenance',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],

            // ────────────────────── CUSTOMER CARE & IT TECHNICIAN SUPPORT ──────────────────────
            [
                'name' => 'Customer Care Support',
                '_slug' => 'customer-care-support',
                'description' => 'Resolves customer care issues for staff.',
                'type' => 'support',
                'level' => 50,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'IT Technician Support',
                '_slug' => 'it-technician-support',
                'description' => 'Resolves IT technician issues for staff.',
                'type' => 'it',
                'level' => 60,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],

            // ────────────────────── SECURITY & CCTV ───────────────────────
            [
                'name' => 'Security Manager',
                '_slug' => 'security-manager',
                'description' => 'Coordinates all security personnel and protocols.',
                'type' => 'security',
                'level' => 70,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 100]),
                '_status' => 1
            ],
            [
                'name' => 'Security Guard',
                '_slug' => 'security-guard',
                'description' => 'Monitors security.',
                'type' => 'security',
                'level' => 40,
                'settings' => json_encode([]),
                'communication_limits' => json_encode(['daily_limit' => 50]),
                '_status' => 1
            ],
        ];

        // Insert roles one by one to handle JSON fields properly
        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                '_slug' => $role['_slug'] ?? strtolower(str_replace(' ', '-', $role['name'])),
                'description' => $role['description'] ?? null,
                'type' => $role['type'] ?? 'custom',
                'level' => $role['level'] ?? 0,
                'settings' => $role['settings'] ?? json_encode([]),
                'communication_limits' => $role['communication_limits'] ?? json_encode([]),
                '_status' => $role['_status'] ?? 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->info('Successfully seeded ' . count($roles) . ' roles!');
    }
}