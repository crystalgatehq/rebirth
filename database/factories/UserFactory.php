<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Ability;
use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('Qwerty123!'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $this->ensureUserHasProfile($user);
            $this->assignRoleToUser($user);
        });
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
        $role = Role::where('_slug', '!=', 'administrator')
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
