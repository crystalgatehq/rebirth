<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' ' . $this->faker->companySuffix();
        
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->boolean(70) ? $this->faker->paragraph() : null,
            'personal_team' => false,
            'status' => $this->faker->randomElement([
                Team::STATUS_INACTIVE,
                Team::STATUS_ACTIVE,
                Team::STATUS_SUSPENDED
            ]),
        ];
    }

    /**
     * Indicate that the team is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Team::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the team is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Team::STATUS_INACTIVE,
        ]);
    }

    /**
     * Indicate that the team is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Team::STATUS_SUSPENDED,
        ]);
    }

    /**
     * Indicate that the team is a personal team.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'personal_team' => true,
            'name' => 'Personal Team',
            'slug' => 'personal-team',
        ]);
    }

}
