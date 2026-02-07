<?php

namespace Database\Factories;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use function fake;

/**
 * @extends Factory<Idea>
 */
class IdeaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'links' => [fake()->url()],
        ];
    }

    /**
     * Create an idea with steps.
     */
    public function withSteps(int $count = 3): static
    {
        return $this->has(
            \App\Models\Step::factory()->count($count),
            'steps'
        );
    }
}
