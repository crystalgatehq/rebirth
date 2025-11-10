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
        
        // 1. Seed Roles → Abilities → Pivot
        $this->call([
            RolesTableSeeder::class,
            AbilitiesTableSeeder::class,
            AbilityRoleTableSeeder::class,
        ]);

        // 2. Get all roles from the database, ordered by hierarchy
        $roles = \App\Models\Role::orderBy('_hierarchy_matrix_level', 'desc')->get();

        // 3. Create a default user for each role
        $allUsers = [];
        
        foreach ($roles as $role) {
            // Sanitize role name to only allow alphanumeric, dots, and hyphens
            $sanitizedName = preg_replace('/[^a-zA-Z0-9. -]/', '', $role->name);
            $emailLocal = strtolower(preg_replace('/[. -]+/', '.', trim($sanitizedName)));
            $email = $emailLocal . '@rebirth.org';
            $user = $this->createUser($role->name, $email, $role->_slug);
            
            // Assign the role to the user
            $user->roles()->sync([$role->id], false);
            
            $allUsers[] = $user;
        }
        
        // 4. Add Africa's Talking settings
        $this->call([
            AfricasTalkingSettingsSeeder::class,
        ]);

        // 5. Run the database fixer to ensure data integrity
        $this->call([
            FixDatabaseSeeder::class,
        ]);
        
        // Output success message
        $this->command->info('✅ Database seeding completed successfully!');
        
        // Prevent any further output
        $this->command->getOutput()->writeln('');
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
    protected function createUser(string $name, string $email, string $roleSlug): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Assign the role
        $user->assignRole($roleSlug);

        // Create a team for the user if requested and they don't have one
        if ($user->ownedTeams()->count() === 0) {
            $teamName = $name . "'s Team";
            $team = $user->ownedTeams()->create([
                '_uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $teamName,
                '_slug' => \Illuminate\Support\Str::slug($teamName),
                'description' => 'Team for ' . $name,
                'personal_team' => false,
                '_status' => Team::ACTIVE
            ]);

            // Attach the user to the team as owner
            $user->teams()->syncWithoutDetach([$team->id => ['role' => 'owner']]);
            
            // Set the user's current team
            $user->current_team_id = $team->id;
            $user->save();
        }

        return $user;
    }
}