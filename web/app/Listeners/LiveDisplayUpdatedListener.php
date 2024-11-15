<?php

namespace App\Listeners;

use App\Events\LiveDisplayUpdatedEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveDisplayUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(LiveDisplayUpdatedEvent $event): void
    {
        $liveDisplayId = $event->liveDisplay->id;

        // Send the update to the live backend
        try {
            $response = Http::post(config("app.LIVE_SERVICE_URL") . "/api/b/live-display/on-update/$liveDisplayId");
        } catch (ConnectionException $e) {
            Log::error($e->getMessage());
        }
    }
}
