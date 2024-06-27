<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'project_id' => Project::factory(), // Assumes you have a Project factory
            'check_by_user_id' => null, // Optionally assign in tests or seeders
            'confirm_by_user_id' => null, // Optionally assign in tests or seeders
            'follow_up_user_id' => null, // Optionally assign in tests or seeders
            'name' => $this->faker->word,
            'description' => $this->faker->text,
            'status' => 'todo',
            'priority' => $this->faker->randomElement(['high', 'low', 'medium']),
            'page_order' => $this->faker->numberBetween(0, 100),
            'follow_up_message' => $this->faker->sentence,
            'proof_method' => $this->faker->word,
            'invoice_reference' => $this->faker->word,
            'estimate_time' => $this->faker->randomDigitNotNull.' hours',
            'deadline' => $this->faker->date(),
            'recurring_period' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'is_mark_as_done' => false,
            'is_checked' => false,
            'is_confirmed' => false,
            'is_archived' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
