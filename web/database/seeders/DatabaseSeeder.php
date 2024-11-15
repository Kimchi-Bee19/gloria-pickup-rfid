<?php

namespace Database\Seeders;

use App\Models\PickupPersonnel;
use App\Models\Student;
use App\Models\StudentIdentity;
use App\Models\StudentPickupPersonnelMapping;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Vehicle;
use App\Models\VehicleIdentity;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create 1000 normal students
        Student::factory(1000)
            ->has(Vehicle::factory()->has(VehicleIdentity::factory(), 'identities'))
            ->has(StudentIdentity::factory(), 'identities')
            ->has(StudentPickupPersonnelMapping::factory()->has(PickupPersonnel::factory(), 'pickup_personnel'), 'student_pickup_personnel_mappings')
            ->create();

        // Create 250 unassigned students
        Student::factory(250)
            ->create();

        // Create 250 unassigned vehicle tags
        VehicleIdentity::factory(250)
            ->create();

        // Create 250 unassigned student tags
        StudentIdentity::factory(250)
            ->create();

        // Create 250 unassigned pickup personnel
        PickupPersonnel::factory(250)
            ->create();

//        $this->call([
//            StudentSeeder::class,
//            VehicleSeeder::class,
//            VehicleIdentitiesSeeder::class,
//            StudentIdentitiesSeeder::class,
//            StudentVehicleMappingsSeeder::class,
//            PickupPersonnelSeeder::class
//        ]);
    }
}
