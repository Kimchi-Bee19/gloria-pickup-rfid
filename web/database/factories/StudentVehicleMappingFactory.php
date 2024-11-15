<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentVehicleMapping;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StudentVehicleMappingFactory extends Factory
{
    protected $model = StudentVehicleMapping::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'student_id' => Student::factory(),
            'vehicle_id' => Vehicle::factory(),
        ];
    }
}
