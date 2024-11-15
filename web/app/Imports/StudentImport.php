<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\StudentIdentity;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        foreach($rows as $row){
            $student = Student::create([
                'internal_id' => $row['internal_id'],
                'full_name' => $row['full_name'],
                'call_name' => $row['call_name'],
                'class' => $row['class'],
            ]);

            if(isset($row['tag_id'])){
                $student_identity = StudentIdentity::create([
                    'tag_id' => $row['tag_id'],
                    'notes' => " ",
                    'student_id' => $student->id,
                ]);
            }
            
            if(isset($row['notes'])){
                $student_identity->update([
                    'notes' => $row['notes'],
                ]);
            }
        }
    }
}
