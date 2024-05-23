<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'uuid' => Str::uuid(),
            'company_logo' => $this->faker->imageUrl(),
            'bg_image' => $this->faker->imageUrl(),
            'bg_color' => $this->faker->hexColor,
            'visibility' => $this->faker->randomElement(['public', 'private', 'restricted']),
            'user_id' => 1,
            'font_color' => $this->faker->hexColor,
            'order' => $this->faker->numberBetween(1, 100),
            'guest_users' => null,
        ];
    }
}