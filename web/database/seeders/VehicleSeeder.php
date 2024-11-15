<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicles = [
            [
                'id' => 1,
                'type' => 'Car',
                'model' => 'Civic',
                'color' => 'Red',
                'license_plate' => 'L 1927 ML',
                'license_plate_expiry' => '2025-05-10',
                'picture_url' => 'https://example.com/vehicles/car1.jpg',
            ],
            [
                'id' => 2,
                'type' => 'Motorcycle',
                'model' => 'Ninja',
                'color' => 'Green',
                'license_plate' => 'L 9837 NL',
                'license_plate_expiry' => '2026-11-20',
                'picture_url' => 'https://example.com/vehicles/motorcycle1.jpg',
            ],
            [
                'id' => 3,
                'type' => 'Truck',
                'model' => 'Ford F-150',
                'color' => 'Black',
                'license_plate' => 'W 1889 LO',
                'license_plate_expiry' => '2023-07-15',
                'picture_url' => 'https://example.com/vehicles/truck1.jpg',
            ],
            [
                'id' => 4,
                'type' => 'Van',
                'model' => 'Odyssey',
                'color' => 'Blue',
                'license_plate' => 'N 1932 BAU',
                'license_plate_expiry' => '2026-02-25',
                'picture_url' => 'https://example.com/vehicles/van1.jpg',
            ],
            [
                'id' => 5,
                'type' => 'SUV',
                'model' => 'CR-V',
                'color' => 'White',
                'license_plate' => 'L 6553 MN',
                'license_plate_expiry' => '2026-10-05',
                'picture_url' => 'https://example.com/vehicles/suv1.jpg',
            ],
            [
                'id' => 6,
                'type' => 'Car',
                'model' => 'Accord',
                'color' => 'Silver',
                'license_plate' => 'L 9837 WH',
                'license_plate_expiry' => '2025-03-14',
                'picture_url' => 'https://example.com/vehicles/car2.jpg',
            ],
            [
                'id' => 7,
                'type' => 'Motorcycle',
                'model' => 'CBR',
                'color' => 'Yellow',
                'license_plate' => 'L 7793 GF',
                'license_plate_expiry' => '2023-12-22',
                'picture_url' => 'https://example.com/vehicles/motorcycle2.jpg',
            ],
            [
                'id' => 8,
                'type' => 'Car',
                'model' => 'Tesla Model S',
                'color' => 'Black',
                'license_plate' => 'L 9555 UY',
                'license_plate_expiry' => '2026-08-19',
                'picture_url' => 'https://example.com/vehicles/tesla.jpg',
            ],
            [
                'id' => 9,
                'type' => 'Motorcycle',
                'model' => 'Harley Davidson',
                'color' => 'Blue',
                'license_plate' => 'L 5258 WH',
                'license_plate_expiry' => '2026-06-14',
                'picture_url' => 'https://example.com/vehicles/harley.jpg',
            ],
            [
                'id' => 10,
                'type' => 'Truck',
                'model' => 'Chevy Silverado',
                'color' => 'Gray',
                'license_plate' => 'W 1777 OLH',
                'license_plate_expiry' => '2025-12-20',
                'picture_url' => 'https://example.com/vehicles/silverado.jpg',
            ],
            [
                'id' => 11,
                'type' => 'Van',
                'model' => 'Mercedes Sprinter',
                'color' => 'White',
                'license_plate' => 'L 1876 MM',
                'license_plate_expiry' => '2026-03-11',
                'picture_url' => 'https://example.com/vehicles/sprinter.jpg',
            ],
            [
                'id' => 12,
                'type' => 'SUV',
                'model' => 'Toyota Land Cruiser',
                'color' => 'Green',
                'license_plate' => 'L 1217 AS',
                'license_plate_expiry' => '2025-11-30',
                'picture_url' => 'https://example.com/vehicles/landcruiser.jpg',
            ]
        ];

        foreach ($vehicles as $vehicle) {
            DB::table('vehicles')->insert(array_merge($vehicle, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
