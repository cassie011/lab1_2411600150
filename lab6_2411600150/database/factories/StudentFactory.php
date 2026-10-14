<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => '24116' . fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'program' => fake()->randomElement(['BS Information Technology', 'BS Computer Science']),
            'year_level' => fake()->numberBetween(1, 4),
            'units' => 21,
            'gpa' => 3.50,
            'attendance_rate' => 100,
        ];
    }
}
