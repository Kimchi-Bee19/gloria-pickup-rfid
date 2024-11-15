<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArrivalDepartureTracking extends Model
{
    public function vehicle_arrival_log()
    {
        return $this->belongsTo(VehicleArrivalLog::class);
    }

    public function student_departure_logs()
    {
        return $this->hasMany(StudentDepartureLog::class);
    }
}
