<?php

namespace Database\Factories;

use App\Models\PickupPersonnel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PickupPersonnelFactory extends Factory
{
    protected $model = PickupPersonnel::class;

    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'receive_notifications' => $this->faker->boolean(),
            'phone_number' => str_replace($this->faker->phoneNumber(), " ", ""),
            'picture_url' => $this->faker->url(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
