<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentIdentity extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'tag_id', 'auth_check', 'notes', 'student_id'];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tagId(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => bin2hex(gettype($value) === "resource" ? stream_get_contents($value) : $value),
            set: fn(mixed $value) => DB::raw("E'\\\\x" . bin2hex(hex2bin($value)) . "'")
        );
    }

    public function departureLogs()
    {
        return $this->hasMany(StudentDepartureLog::class);
    }

}
