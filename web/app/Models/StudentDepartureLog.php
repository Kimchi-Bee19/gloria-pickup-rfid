<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDepartureLog extends Model
{
    public function student()
        {
            return $this->belongsTo(Student::class);
        }

    public function student_identity()
    {
        return $this->belongsTo(StudentIdentity::class);
    }

    public function arrival_departure_tracking()
    {
        return $this->belongsTo(ArrivalDepartureTracking::class);
    }
}
