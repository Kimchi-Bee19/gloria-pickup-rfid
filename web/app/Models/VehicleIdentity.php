<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleIdentity extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'tag_id',
        'notes',
        'vehicle_id',
        'auth_check'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function tagId(): Attribute
    {
        $connection = $this->getConnection();

        return Attribute::make(
            get: fn(mixed $value) => bin2hex(gettype($value) === "resource" ? stream_get_contents($value) : $value),
            // This is a hacky method.
            set: fn(mixed $value) => DB::raw("E'\\\\x" . bin2hex(hex2bin($value)) . "'")
    );
    }

    public function authCheck(): Attribute
    {
        $connection = $this->getConnection();
        
        return Attribute::make(
            get: fn(mixed $value) => bin2hex(gettype($value) === "resource" ? stream_get_contents($value) : $value),
            set: fn(mixed $value) => DB::raw("E'\\\\x" . bin2hex(hex2bin($value)) . "'")
        );
    }

    public function arrival_logs()
    {
        return $this->hasMany(VehicleArrivalLog::class);
    }
}
