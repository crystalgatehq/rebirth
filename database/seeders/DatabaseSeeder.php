<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Team;
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
        // 1. Seed Roles → Abilities → Pivot
        $this->call([
            RolesTableSeeder::class,
            AbilitiesTableSeeder::class,
            AbilityRoleTableSeeder::class,
        ]);

        // 2. Get all roles from the database, ordered by hierarchy
        $roles = \App\Models\Role::orderBy('_hierarchy_matrix_level', 'desc')->get();

        // 3. Create a default user and team for each role
        $allUsers = [];
        $roleTeams = [];
        
        // First pass: Create one user for each role (these will be team owners)
        $ownerUsers = [];
        foreach ($roles as $role) {
            // Create owner user for this role
            // Sanitize role name to only allow alphanumeric, dots, and hyphens
            $sanitizedName = preg_replace('/[^a-zA-Z0-9. -]/', '', $role->name);
            $emailLocal = strtolower(preg_replace('/[. -]+/', '.', trim($sanitizedName)));
            $ownerEmail = $emailLocal . '@psilocybin.org';
            $ownerUser = $this->createUser($role->name, $ownerEmail, $role->_slug, false);
            $allUsers[] = $ownerUser;
            $ownerUsers[$role->id] = $ownerUser;
        }
        
        // Truncate teams table and reset auto-increment
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Team::truncate();
        \DB::statement('ALTER TABLE teams AUTO_INCREMENT = 1;');
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Second pass: Create teams and assign users
        foreach ($roles as $role) {
            $teamName = \Illuminate\Support\Str::plural($role->name);
            
            // Get the owner user for this role
            $ownerUser = $ownerUsers[$role->id] ?? null;
            if (!$ownerUser) continue;
            
            // Create team for this role
            $team = \App\Models\Team::create([
                'name' => $teamName,
                '_slug' => \Illuminate\Support\Str::slug($teamName),
                'description' => "Team for {$role->name} role",
                'user_id' => $ownerUser->id,
                'personal_team' => false,
                '_status' => $role->_status === \App\Models\Role::ACTIVE 
                    ? \App\Models\Team::ACTIVE 
                    : \App\Models\Team::SUSPENDED,
                'role_id' => $role->id,
                '_uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);
            
            // Store the team for this role
            $roleTeams[$role->id] = $team;
            
            // Assign the owner user to the team as owner
            $team->users()->sync([$ownerUser->id => ['role' => 'owner']], false);
            
            // Assign the role to the owner user
            $ownerUser->roles()->sync([$role->id], false);
            
            // Get all users who are not the owner of this team
            $memberUsers = collect($allUsers)->filter(function($user) use ($ownerUser) {
                return $user->id !== $ownerUser->id;
            });
            
            // Add all other users as members of this team
            foreach ($memberUsers as $memberUser) {
                $team->users()->sync([$memberUser->id => ['role' => 'member']], false);
                // Don't change the user's role, just add them to the team
            }
        } // End of roles foreach loop
        
        // 4. Run the database fixer to ensure data integrity
        $this->call([
            FixDatabaseSeeder::class,
        ]);
    }

    /**
     * Create a user, assign a role, and create a team.
     */
    private function createUser(string $name, string $email, string $roleSlug, bool $createTeam = true): User
    {
        // Create the user
        $user = User::factory()->create([
            'name'  => $name,
            'email' => $email,
        ]);

        // Assign the role
        $user->assignRole($roleSlug);

        // Create a team for the user if requested and they don't have one
        if ($createTeam && $user->ownedTeams()->count() === 0) {
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