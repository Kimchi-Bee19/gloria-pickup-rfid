<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleIdentityFactory extends Factory
{
    protected $model = VehicleIdentity::class;

    public function definition(): array
    {
        return [
            'type' => "uhf_rfid",
            'tag_id' => bin2hex(random_bytes(7)),
            'notes' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
