<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 10,
            'internal_id' => 1,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 31,
            'internal_id' => 2,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 12,
            'internal_id' => 3,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 13,
            'internal_id' => 4,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 14,
            'internal_id' => 5,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 15,
            'internal_id' => 6,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 16,
            'internal_id' => 7,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);


        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 17,
            'internal_id' => 8,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 18,
            'internal_id' => 9,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 19,
            'internal_id' => 10,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 20,
            'internal_id' => 11,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 21,
            'internal_id' => 12,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $name = fake()->name();
        DB::table('students')->insert([
            'id' => 22,
            'internal_id' => 13,
            'full_name' => $name,
            'call_name' => $name,
            'class' => 'A3',
            'picture_url' => fake()->text(10),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }
}
