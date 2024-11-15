<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPickupPersonnelMapping extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'pickup_personnel_id',
        'relationship_to_student'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function pickup_personnel() {
        return $this->belongsTo(PickupPersonnel::class);
    }
}
