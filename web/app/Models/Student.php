<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        "internal_id",
        "full_name",
        "call_name",
        "class",
        "picture_url",
    ];

    /**
     * Get all tags associated with the student.
     */

    public function identities(): HasMany
    {
        return $this->hasMany(StudentIdentity::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(StudentGroup::class, 'student_group_mappings', 'student_id', 'tag_id');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'student_vehicle_mappings', 'student_id', 'vehicle_id');
    }

    public function student_pickup_personnel_mappings(): HasMany
    {
        return $this->hasMany(StudentPickupPersonnelMapping::class);
    }
    
    public function student_vehicle_mappings()
    {
        return $this->hasMany(StudentVehicleMapping::class);
    }

    public function pickupPersonnels(): BelongsToMany
    {
        return $this->belongsToMany(PickupPersonnel::class, 'student_pickup_personnel_mappings', 'student_id', 'pickup_personnel_id');
    }

    public function departure_logs()
    {
        return $this->hasMany(VehicleArrivalLog::class);
    }
}
