<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'model' => $this->faker->word(),
            'color' => $this->faker->colorName(),
            'license_plate' => strtoupper($this->faker->randomLetter()) . " " . $this->faker->randomNumber(4) . " " . strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()),
            'license_plate_expiry' => Carbon::now()->addYears(5),
            'picture_url' => $this->faker->imageUrl(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
