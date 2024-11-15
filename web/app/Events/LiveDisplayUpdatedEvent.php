<?php

namespace App\Events;

use App\Models\LiveDisplay;
use Illuminate\Foundation\Events\Dispatchable;

class LiveDisplayUpdatedEvent
{
    use Dispatchable;

    public function __construct(public LiveDisplay $liveDisplay)
    {
    }
}
