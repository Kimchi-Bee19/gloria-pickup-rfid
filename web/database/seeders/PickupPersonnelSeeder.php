<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PickupPersonnelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pickup_personnels = [
            [
                "full_name" => "John Doe",
                "receive_notifications" => true,
                "phone_number" => "081234567890",
                "picture_url" => "https://example.com/pictures/john_doe.jpg"
            ],
            [
                "full_name" => "Jane Smith",
                "receive_notifications" => false,
                "phone_number" => "081234567891",
                "picture_url" => "https://example.com/pictures/jane_smith.jpg"
            ],
            [
                "full_name" => "Robert Brown",
                "receive_notifications" => true,
                "phone_number" => "081234567892",
                "picture_url" => "https://example.com/pictures/robert_brown.jpg"
            ],
            [
                "full_name" => "Emily White",
                "receive_notifications" => false,
                "phone_number" => "081234567893",
                "picture_url" => "https://example.com/pictures/emily_white.jpg"
            ],
            [
                "full_name" => "Michael Green",
                "receive_notifications" => true,
                "phone_number" => "081234567894",
                "picture_url" => "https://example.com/pictures/michael_green.jpg"
            ],
            [
                "full_name" => "Sarah Black",
                "receive_notifications" => false,
                "phone_number" => "081234567895",
                "picture_url" => "https://example.com/pictures/sarah_black.jpg"
            ],
            [
                "full_name" => "David Johnson",
                "receive_notifications" => true,
                "phone_number" => "081234567896",
                "picture_url" => "https://example.com/pictures/david_johnson.jpg"
            ],
            [
                "full_name" => "Laura Davis",
                "receive_notifications" => false,
                "phone_number" => "081234567897",
                "picture_url" => "https://example.com/pictures/laura_davis.jpg"
            ],
            [
                "full_name" => "James Wilson",
                "receive_notifications" => true,
                "phone_number" => "081234567898",
                "picture_url" => "https://example.com/pictures/james_wilson.jpg"
            ],
            [
                "full_name" => "Linda Martinez",
                "receive_notifications" => false,
                "phone_number" => "081234567899",
                "picture_url" => "https://example.com/pictures/linda_martinez.jpg"
            ]
        ];        

        foreach ($pickup_personnels as $personnel) {
            DB::table('pickup_personnels')->insert(array_merge($personnel, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
