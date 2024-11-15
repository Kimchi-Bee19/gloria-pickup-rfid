<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentVehicleMappingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 10,
            'vehicle_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 31,
            'vehicle_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 12,
            'vehicle_id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 13,
            'vehicle_id' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 14,
            'vehicle_id' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 15,
            'vehicle_id' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 16,
            'vehicle_id' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 17,
            'vehicle_id' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 18,
            'vehicle_id' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 19,
            'vehicle_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 20,
            'vehicle_id' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 21,
            'vehicle_id' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        DB::table('student_vehicle_mappings')->insert([
            'student_id' => 22,
            'vehicle_id' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
