<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityReader extends Model
{
    protected $fillable = [
        "label",
        "username",
        "type"
    ];

    protected $hidden = [
        "password_hash"
    ];

    protected $casts = [
        "last_login" => "datetime",
        "password_hash" => "hashed"
    ];
}
