<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable query logging to prevent memory issues
        DB::disableQueryLog();

        // 1. Seed Settings → AfricasTalkingSettingsSeeder
        $this->call([
            SettingsTableSeeder::class,
            AfricasTalkingSettingsSeeder::class,
        ]);
        
        // 2. Seed Roles → Abilities → Pivot
        $this->call([
            RolesTableSeeder::class,
            AbilitiesTableSeeder::class,
            AbilityRoleTableSeeder::class,
        ]);

        // 3. Create test users for each role if they don't exist
        $testUsers = [
            'administrator' => [
                'email' => 'admin@rebirth.org',
                'name' => 'Admin User'
            ],
            'general-manager' => [
                'email' => 'manager@rebirth.org',
                'name' => 'General Manager'
            ],
            'operations-manager' => [
                'email' => 'operations@rebirth.org',
                'name' => 'Operations Manager'
            ],
            'support-agent' => [
                'email' => 'support@rebirth.org',
                'name' => 'Support Agent'
            ],
            'security-personnel' => [
                'email' => 'security@rebirth.org',
                'name' => 'Security Personnel'
            ],
            'content-moderator' => [
                'email' => 'moderator@rebirth.org',
                'name' => 'Content Moderator'
            ],
            'registered-user' => [
                'email' => 'user@rebirth.org',
                'name' => 'Registered User'
            ],
            'guest' => [
                'email' => 'guest@rebirth.org',
                'name' => 'Guest User'
            ]
        ];
        
        foreach ($testUsers as $roleSlug => $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Assign the role if not already assigned
            $role = \App\Models\Role::where('slug', $roleSlug)->first();
            if ($role && !$user->hasRole($roleSlug)) {
                $user->assignRole($role);
            }

            // Ensure the user has a team
            if ($user->teams()->count() === 0) {
                $teamName = $userData['name'] . "'s Team";
                $team = $user->ownedTeams()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $teamName,
                    'slug' => \Illuminate\Support\Str::slug($teamName),
                    'description' => 'Team for ' . $userData['name'],
                    'personal_team' => false,
                    'status' => Team::STATUS_ACTIVE
                ]);

                // Attach the user to the team as owner
                $user->teams()->attach($team->id, [
                    'play' => \App\Models\TeamUser::OWNER,
                    'status' => \App\Models\TeamUser::STATUS_ACTIVE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Set the user's current team
                $user->current_team_id = $team->id;
                $user->save();
            }
        }
        
        // 4. Seed geographic data
        $this->call([
            CountiesTableSeeder::class,
            FactoriesTableSeeder::class,
            CommunicationTypesTableSeeder::class,
            CommunicationCategoriesTableSeeder::class,
        ]);

        // 5. Run the database fixer to ensure data integrity
        $this->call([
            \Database\Seeders\FixDatabaseSeeder::class,
        ]);
        
        // Clear the console output
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            system('cls');
        } else {
            system('clear');
        }
        
        // Output the clean seeding report
        echo \Database\Seeders\Helpers\DatabaseSeederReport::generate();
        
        // Prevent any further output
        exit(0);
    }

    /**
     * Create a user with the given name, email, and role.
     *
     * @param string $name
     * @param string $email
     * @param string $roleSlug
     * @return \App\Models\User
     */
    /**
     * Create a user with the given name, email, and role.
     *
     * @param string $name
     * @param string $email
     * @param string $roleSlug
     * @return \App\Models\User
     */
    protected function createUser(string $name, string $email, string $roleSlug): User
    {
        // Check if user already exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Create new user if not exists
            $user = User::factory()->create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create a team for the user if they don't have one
            if ($user->ownedTeams()->count() === 0) {
                $teamName = $name . "'s Team";
                $team = $user->ownedTeams()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $teamName,
                    'slug' => \Illuminate\Support\Str::slug($teamName),
                    'description' => 'Team for ' . $name,
                    'personal_team' => false,
                    'status' => Team::STATUS_ACTIVE
                ]);

                // Attach the user to the team as owner
                $user->teams()->attach($team->id, [
                    'play' => \App\Models\TeamUser::OWNER,
                    'status' => \App\Models\TeamUser::STATUS_ACTIVE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Set the user's current team
                $user->current_team_id = $team->id;
                $user->save();
            }
        }

        // Assign the role if not already assigned
        if (!$user->hasRole($roleSlug)) {
            $user->assignRole($roleSlug);
        }

        return $user;
    }
    
    /**
     * Create a test user with the given role.
     *
     * @param string $roleSlug
     * @param string $email
     * @return \App\Models\User
     */
    protected function createTestUser(string $roleSlug, string $email): User
    {
        $name = ucwords(str_replace('-', ' ', $roleSlug)) . ' User';
        
        // Check if user already exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create a team for the user if they don't have one
            if ($user->ownedTeams()->count() === 0) {
                $teamName = $name . "'s Team";
                $team = $user->ownedTeams()->create([
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'name' => $teamName,
                    'slug' => \Illuminate\Support\Str::slug($teamName),
                    'description' => 'Team for ' . $name,
                    'personal_team' => false,
                    'status' => Team::STATUS_ACTIVE
                ]);

                // Attach the user to the team as owner
                $user->teams()->attach($team->id, [
                    'play' => \App\Models\TeamUser::OWNER,
                    'status' => \App\Models\TeamUser::STATUS_ACTIVE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Set the user's current team
                $user->current_team_id = $team->id;
                $user->save();
            }
        }

        // Assign the role if not already assigned
        if (!$user->hasRole($roleSlug)) {
            $user->assignRole($roleSlug);
        }

        return $user;
    }
}