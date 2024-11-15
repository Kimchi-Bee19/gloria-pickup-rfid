<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehicleIdentity;
use Illuminate\Support\Collection;
use App\Models\StudentVehicleMapping;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleImport implements ToCollection,  WithHeadingRow
{
    
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {

            $vehicle = Vehicle::create([
                'license_plate' => $row['license_plate'],
                'type' => $row['type'],
                'model' => $row['model'],
                'color' => $row['color'],
            ]);

            if(isset($row['license_plate_expiry'])){
                $date = is_int($row['license_plate_expiry']) 
                    ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['license_plate_expiry']))
                    : Carbon::createFromFormat('m/Y',$row['license_plate_expiry']);
               
                $vehicle->update([
                    'license_plate_expiry' =>$date,
                ]);
            }


            if(isset($row['tag_id'])){
                $vehicle_identity = VehicleIdentity::create([
                    'tag_id' => $row['tag_id'],
                    'notes' => " ",
                    'vehicle_id' => $vehicle->id,
                ]);
            }
            
            if(isset($row['notes'])){
                $vehicle_identity->update([
                    'notes' => $row['notes'],
                ]);
            }

            $index = 1;

            while(isset($row['internal_id'.$index]) && isset($row['full_name'.$index]) && isset($row['call_name'.$index]) && isset($row['class'.$index])){
                if($row['internal_id'.$index] != "" && $row['full_name'.$index] != "" && $row['call_name'.$index] != "" && $row['class'.$index] != ""){
                    $student = Student::create([
                        'internal_id' => $row['internal_id'.$index],
                        'full_name' => $row['full_name'.$index],
                        'class' => $row['class'.$index],
                        'call_name' => $row['call_name'.$index]
                    ]);

                    StudentVehicleMapping::create([
                        'student_id' => $student->id,
                        'vehicle_id' => $vehicle->id
                    ]);
                }

                $index++;
            }
        }
    }
}
