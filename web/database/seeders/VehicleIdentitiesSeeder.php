<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleIdentitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicleIdentities = [
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xA1B2C3D4E5F6\''), // 12-byte binary data
                'notes' => 'Vehicle 1 RFID tag',
                'vehicle_id' => 1,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xB2C3D4E5F6A1\''), // 12-byte binary data
                'notes' => 'Vehicle 2 RFID tag',
                'vehicle_id' => 2,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xC3D4E5F6A1B2\''), // 12-byte binary data
                'notes' => 'Vehicle 3 RFID tag',
                'vehicle_id' => 3,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xD4E5F6A1B2C3\''), // 12-byte binary data
                'notes' => 'Vehicle 4 RFID tag',
                'vehicle_id' => 4,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xE5F6A1B2C3D4\''), // 12-byte binary data
                'notes' => 'Vehicle 5 RFID tag',
                'vehicle_id' => 5,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xF6A1B2C3D4E5\''), // 12-byte binary data
                'notes' => 'Vehicle 6 RFID tag',
                'vehicle_id' => 6,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xA7B8C9D0E1F2\''), // 12-byte binary data
                'notes' => 'Vehicle 7 RFID tag',
                'vehicle_id' => 7,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xB8C9D0E1F2A7\''), // 12-byte binary data
                'notes' => 'Vehicle 8 RFID tag',
                'vehicle_id' => 8,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xC9D0E1F2A7B8\''), // 12-byte binary data
                'notes' => 'Vehicle 9 RFID tag',
                'vehicle_id' => 9,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xD0E1F2A7B8C9\''), // 12-byte binary data
                'notes' => 'Vehicle 10 RFID tag',
                'vehicle_id' => 10,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xE1F2A7B8C9D0\''), // 12-byte binary data
                'notes' => 'Vehicle 11 RFID tag',
                'vehicle_id' => 11,
            ],
            [
                'type' => 'uhf_rfid',
                'tag_id' => DB::raw('E\'\\\\xF2A7B8C9D0E1\''), // 12-byte binary data
                'notes' => 'Vehicle 12 RFID tag',
                'vehicle_id' => 12,
            ]
        ];

        foreach ($vehicleIdentities as $identity) {
            DB::table('vehicle_identities')->insert(array_merge($identity, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
