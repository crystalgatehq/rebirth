<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Str;
use Database\Factories\TeamFactory;
use Illuminate\Support\Facades\Hash;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Indicate that the user is an admin.
     */
    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin User',
            'email' => 'admin@rebirth.org',
        ]);
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager()
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Manager User',
            'email' => 'manager@rebirth.org',
        ]);
    }

    /**
     * Indicate that the user is a regular user.
     */
    public function regular()
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Regular User',
            'email' => 'user@rebirth.org',
        ]);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $counter = 1;
        
        return [
            'name' => 'User ' . $counter,
            'email' => 'user' . $counter++ . '@rebirth.org',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory.
     */
    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $this->ensureUserHasTeam($user);
            $this->ensureUserHasProfile($user);
            $this->assignRoleToUser($user);
            
            // Add user to additional teams (20% chance for each user)
            if ($this->faker->boolean(20)) {
                $this->addUserToOtherTeams($user);
            }
        });
    }
    
    /**
     * Add user to all other teams except their own teams.
     *
     * @param User $user
     * @return void
     */
    protected function addUserToOtherTeams(User $user): void
    {
        $teams = Team::where('owner_id', '!=', $user->id) // Don't add to own teams
            ->where('personal_team', false)
            ->get();

        foreach ($teams as $team) {
            $team->addMember($user);
        }
    }

    /**
     * Ensure the user has a profile.
     */
    protected function ensureUserHasProfile(User $user): void
    {
        if (!$user->profile) {
            ProfileFactory::new()
                ->for($user)
                ->create(['user_id' => $user->id]);
        }
    }

    /**
     * Ensure the user has a team and necessary associations.
     */
    protected function ensureUserHasTeam(User $user): void
    {
        if ($user->ownedTeams()->exists()) {
            return;
        }

        $team = TeamFactory::new()
            ->for($user, 'owner')
            ->create([
                'name' => "{$user->name}'s Team",
                'slug' => Str::slug("{$user->name}-team"),
                'personal_team' => false,
                'status' => 1, // 1 = active
            ]);

        $user->update(['current_team_id' => $team->id]);
    
    }

    /**
     * Assign a role to the user
     *
     * @param User $user
     * @return void
     */
    protected function assignRoleToUser(User $user): void
    {
        // If the user already has a role, don't reassign
        if ($user->roles()->exists()) {
            return;
        }

        // Get a random role (excluding administrator for security)
        $role = Role::where('slug', '!=', 'administrator')
            ->inRandomOrder()
            ->first();

        if ($role) {
            $user->roles()->attach($role);
        }
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
