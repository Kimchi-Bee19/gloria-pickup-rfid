<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleArrivalLog extends Model
{
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicle_identity()
    {
        return $this->belongsTo(VehicleIdentity::class);
    }

    public function arrival_departure_tracking()
    {
        return $this->hasOne(ArrivalDepartureTracking::class);
    }
}
