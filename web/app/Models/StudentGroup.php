<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGroup extends Model
{
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_group_mappings', 'tag_id', 'student_id');
    }
}
