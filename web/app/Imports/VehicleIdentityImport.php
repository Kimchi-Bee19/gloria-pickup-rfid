<?php

namespace App\Imports;

use App\Models\VehicleIdentity;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleIdentityImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $vehicle_identity = VehicleIdentity::create([
                'tag_id' => $row['tag_id'],
                'notes' => " ",
            ]);

            if (isset($row['notes'])) {
                $vehicle_identity->update([
                    'notes' => $row['notes'],
                ]);
            }

            if (isset($row['auth_check'])) {
                $vehicle_identity->update([
                    'auth_check' => $row['auth_check'],
                ]);
            }
        }
    }
}
