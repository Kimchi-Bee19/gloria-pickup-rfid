<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        return [
            'internal_id' => $this->faker->unique()->regexify('[A-Z0-9]{16}'), // Random 16 character alphanumeric ID
            'full_name' => $firstName . ' ' . $lastName,
            'call_name' => $firstName,
            'class' => $this->faker->randomElement(
                [
                    "TK " . $this->faker->randomElement(["A1", "B1", "A2", "B2"]),
                    "SD " . $this->faker->randomElement(["1", "2", "3", "4", "5", "6"]) . $this->faker->randomElement(["A", "B", "C"]),
                    "SMP " . $this->faker->randomElement(["1", "2", "3"]) . $this->faker->randomElement(["A", "B", "C"])
                ]
            ),
            'picture_url' => $this->faker->imageUrl(256, 256, 'people', true), // Random image URL
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
