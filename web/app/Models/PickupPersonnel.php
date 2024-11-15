<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentPickupPersonnelMapping;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupPersonnel extends Model
{
    use HasFactory;
    protected $fillable = [
        'pickup_personnel_id',
        'receive_notifications',
        'full_name',
        'phone_number',
        'picture_url',
        'notes'
    ];

    public function student_pickup_personnel_mappings():HasMany
    {
        return $this->hasMany(StudentPickupPersonnelMapping::class);
    }

    public function students(){
        return $this->belongsToMany(Student::class, 'student_pickup_personnel_mappings', 'pickup_personnel_id', 'student_id');
    }
}
