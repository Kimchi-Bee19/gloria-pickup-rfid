<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\PickupPersonnel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\StudentPickupPersonnelMapping;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PickupPersonnelImport implements ToCollection,  WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $pickup_personnel = PickupPersonnel::create([
                'full_name' => $row['full_name'],
            ]);

            if (isset($row['phone_number'])) {
                $pickup_personnel->update([
                    'phone_number' => $row['phone_number'],
                ]);
            }

            if (isset($row['receive_notifications'])) {
                $pickup_personnel->update([
                    'receive_notifications' => $row['receive_notifications'],
                ]);
            }

            $index = 1;

            while (isset($row['internal_id' . $index]) && isset($row['full_name' . $index]) && isset($row['call_name' . $index]) && isset($row['class' . $index]) && isset($row['relationship_to_student' . $index])) {
                if ($row['internal_id' . $index] != "" && $row['full_name' . $index] != "" && $row['call_name' . $index] != "" && $row['class' . $index] != "") {
                    $student = Student::create([
                        'internal_id' => $row['internal_id' . $index],
                        'full_name' => $row['full_name' . $index],
                        'class' => $row['class' . $index],
                        'call_name' => $row['call_name' . $index],
                    ]);

                    StudentPickupPersonnelMapping::create([
                        'student_id' => $student->id,
                        'pickup_personnel_id' => $pickup_personnel->id,
                        'relationship_to_student' => $row['relationship_to_student' . $index],
                    ]);
                }

                $index++;
            }
        }
    }
}
