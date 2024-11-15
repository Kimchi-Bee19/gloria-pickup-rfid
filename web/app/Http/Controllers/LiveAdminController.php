<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vehicle;
use App\Models\LiveDisplay;
use Illuminate\Http\Request;
use App\Models\VehicleIdentity;
use App\Models\VehicleArrivalLog;
use Illuminate\Support\Facades\DB;
use App\Models\StudentVehicleMapping;
use App\Models\ArrivalDepartureTracking;
use Faker\Provider\pl_PL\LicensePlate;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class LiveAdminController extends Controller
{
    public function getLicensePlate()
    {
        $vehicle = Vehicle::select('license_plate', 'id')->get()->sortBy('license_plate')->values(); 
        return response()->json($vehicle);
    }
    public function statusReader()
    {
        // Count the number of connected and disconnected live displays
        $connectedCount = LiveDisplay::where('is_enabled', true)->count();
        $disconnectedCount = LiveDisplay::where('is_enabled', false)->count();

        return response()->json([
            'connected' => $connectedCount,
            'disconnected' => $disconnectedCount,
        ]);
    }
    
    public function getNewEntry(Request $request)
    {
        // Get the ID from the request
        $id = $request->input('id');
        
        // Validate the ID
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $response = Http::post(config("app.LIVE_SERVICE_URL") . "/api/b/live-display/new-entry/$id");

        return $response;
    }

    public function markDeparted(Request $request)
    {
        // Get the ID from the request
        $id = $request->input('id');
        
        // Validate the ID
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $response = Http::post(config("app.LIVE_SERVICE_URL") . "/api/b/live-display/mark-departed/$id");

        return $response;
    }

    public function changeOrder(Request $request)
    {
        // Get the ID from the request
        $id = $request->input('id');
        
        // Validate the ID
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID is required'], 400);
        }

        $response = Http::post(config("app.LIVE_SERVICE_URL") . "/api/b/live-display/change-order/$id");

        return $response;
    }
}
