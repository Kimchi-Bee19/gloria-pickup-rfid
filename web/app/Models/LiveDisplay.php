<?php

namespace App\Models;

use App\Events\LiveDisplayUpdatedEvent;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveDisplay extends Model
{
    use Notifiable;

    protected $fillable = [
        "label",
        "title",
        "group_regex_filter",
        "mqtt_client_id",
        "class_regex_filter",
        "filter_mode",
        "is_enabled",
        "fingerprint"
    ];

    public function fingerprint(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value) => bin2hex(gettype($value) === "resource" ? stream_get_contents($value) : $value),
            set: fn(mixed $value) => DB::raw("E'\\\\x" . bin2hex(hex2bin($value)) . "'")
        );
    }

    protected $dispatchesEvents = [
        "updated" => LiveDisplayUpdatedEvent::class,
        "deleted" => LiveDisplayUpdatedEvent::class,
    ];
}
