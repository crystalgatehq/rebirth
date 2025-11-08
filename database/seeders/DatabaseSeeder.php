<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
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
            $user = $this->createUser($role->name, $email, $role->_slug, false);
            
            // Assign the role to the user
            $user->roles()->sync([$role->id], false);
            
            $allUsers[] = $user;
        }
        
        // 4. Run the database fixer to ensure data integrity
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
     * @param bool $randomizeEmail
     * @return \App\Models\User
     */
    protected function createUser(string $name, string $email, string $roleSlug, bool $randomizeEmail = true): User
    {
        if ($randomizeEmail) {
            $email = str_replace('@', rand(1000, 9999) . '@', $email);
        }

        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        return $user;
    }
}