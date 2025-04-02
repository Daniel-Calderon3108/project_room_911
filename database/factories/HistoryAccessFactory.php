<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HistoryAccess>
 */
class HistoryAccessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => fake()->numberBetween(1, 5),
            'employee_name_complete' => fake()->name(),
            'success' => fake()->boolean(),
            'reason' => fake()->word(),
        ];
    }
}
