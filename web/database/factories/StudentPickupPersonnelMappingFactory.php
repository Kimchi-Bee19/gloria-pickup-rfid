<?php

namespace Database\Factories;

use App\Models\PickupPersonnel;
use App\Models\Student;
use App\Models\StudentPickupPersonnelMapping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StudentPickupPersonnelMappingFactory extends Factory
{
    protected $model = StudentPickupPersonnelMapping::class;

    public function definition(): array
    {
        return [
            'relationship_to_student' => $this->faker->randomElement(["family member", "mom", "dad", "relative"]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'pickup_personnel_id' => PickupPersonnel::factory(),
            'student_id' => Student::factory(),
        ];
    }
}
