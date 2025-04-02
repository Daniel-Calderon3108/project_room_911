<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'last_name' => fake()->lastName(),
            'department_id' => fake()->numberBetween(1, 5),
            'user_id' => User::factory()->create([
                'name' => fake()->name(),
                'password' => bcrypt('12345'), // password
                'active' => true,
                'role_id' => 1,
            ])->id,
        ];
    }
}
