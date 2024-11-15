<?php

namespace App\Imports;

use App\Models\StudentIdentity;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentIdentityImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach($rows as $row){
            $student_identity = StudentIdentity::create([
                'tag_id' => $row['tag_id'],
                'notes' => " ",
            ]);
    
            if(isset($row['notes'])){
                $student_identity->update([
                    'notes' => $row['notes'],
                ]);
            }
        }
    }
}
