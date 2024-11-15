<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'model',
        'color',
        'license_plate',
        'license_plate_expiry',
        'picture_url',
    ];

    public function identities()
    {
        return $this->hasMany(VehicleIdentity::class);
    }

    public function arrival_logs()
    {
        return $this->hasMany(VehicleArrivalLog::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_vehicle_mappings', 'vehicle_id', 'student_id');
    }

    public function arrival_departure_trackings()
    {
        return $this->hasManyThrough(ArrivalDepartureTracking::class, VehicleArrivalLog::class);
    }

    public function student_vehicle_mappings()
    {
        return $this->hasMany(StudentVehicleMapping::class);
    }
}
