<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StudentIdentityFactory extends Factory
{
    protected $model = StudentIdentity::class;

    public function definition(): array
    {
        return [
            'type' => "nfc",
            'tag_id' => bin2hex(random_bytes(16)),
            'notes' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'student_id' => Student::factory(),
        ];
    }
}
