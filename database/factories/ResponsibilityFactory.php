<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Responsibility>
 */
class ResponsibilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
//            bs() only works if the faker locale is set en_US
            "name" => fake()->bs(),
            "role_id" => fake()->numberBetween(1, 50)
        ];
    }
}
