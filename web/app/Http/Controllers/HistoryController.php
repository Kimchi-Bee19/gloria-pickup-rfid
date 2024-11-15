<?php

namespace App\Http\Controllers;

use App\Models\ArrivalDepartureTracking;
use App\Models\StudentDepartureLog;
use App\Models\VehicleArrivalLog;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    //
    public function index_arrival(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $arrivals = VehicleArrivalLog::paginate($perPage)->appends(['perPage' => $perPage]);

        return view("history.arrival", compact("arrivals", "perPage"));
    }

    public function search_arrival(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('perPage', 10);
    
        if ($search) {
            $arrivals = VehicleArrivalLog::where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where("id", $search);
                }
    
                $query->orWhereHas('vehicle', function($vehicleQuery) use ($search) {
                    $vehicleQuery->where('license_plate', 'ILIKE', '%' . $search . '%')
                        ->orWhere('type', 'ILIKE', '%' . $search . '%')
                        ->orWhere('model', 'ILIKE', '%' . $search . '%')
                        ->orWhere('color', 'ILIKE', '%' . $search . '%');
                })
                ->orWhereHas('vehicle_identity', function($identityQuery) use ($search) {
                    $identityQuery->whereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%']);
                });
            })->paginate($perPage);            
        } else {
            return redirect('/arrival-log');
        }
    
        return view('history.arrival', compact('arrivals', 'search', 'perPage'));
    }    

    public function index_departure(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $departures = StudentDepartureLog::paginate($perPage)->appends(['perPage' => $perPage]);

        return view("history.departure", compact("departures", "perPage"));
    }

    public function search_departure(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('perPage', 10);
    
        if ($search) {
            $departures = StudentDepartureLog::where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where("id", $search);
                }
    
                $query->orWhereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('full_name', 'ILIKE', '%' . $search . '%')
                        ->orWhere('internal_id', 'ILIKE', '%' . $search . '%')
                        ->orWhere('class', 'ILIKE', '%' . $search . '%');
                })
                ->orWhereHas('student_identity', function ($studentIdentityQuery) use ($search) {
                    $studentIdentityQuery->whereRaw("encode(tag_id, 'hex') ILIKE ?", ['%' . strtolower($search) . '%']);
                });
            })->paginate($perPage);
        } else {
            return redirect('/departure-log');
        }
    
        return view('history.departure', compact('departures', 'search', 'perPage'));
    }
    

    public function index_tracking(Request $request)
    {
        $perPage = $request->input("perPage", 10);
        $trackings = ArrivalDepartureTracking::paginate($perPage)->appends(['perPage' => $perPage]);

        return view("history.tracking", compact("trackings", "perPage"));
    }

    public function search_tracking(Request $request)
    {
        $search = $request->search;
        $perPage = $request->input('perPage', 10);
    
        if ($search) {
            $trackings = ArrivalDepartureTracking::where(function ($query) use ($search) {
                if (is_numeric($search)) {
                    $query->where("id", $search)
                          ->orWhere("announced_count", $search);
                }
    
                $query->orWhereHas('vehicle_arrival_log.vehicle_identity', function ($query) use ($search) {
                    $query->where('tag_id', 'ILIKE', '%' . $search . '%');
                })
                ->orWhereHas('vehicle_arrival_log.vehicle', function ($query) use ($search) {
                    $query->where('type', 'ILIKE', '%' . $search . '%')
                          ->orWhere('model', 'ILIKE', '%' . $search . '%')
                          ->orWhere('color', 'ILIKE', '%' . $search . '%')
                          ->orWhere('license_plate', 'ILIKE', '%' . $search . '%');
                })
                ->orWhereHas('student_departure_log.student_identity', function ($query) use ($search) {
                    $query->where('tag_id', 'ILIKE', '%' . $search . '%');
                })
                ->orWhereHas('student_departure_log.student', function ($query) use ($search) {
                    $query->where('full_name', 'ILIKE', '%' . $search . '%')
                          ->orWhere('class', 'ILIKE', '%' . $search . '%');
                });
            })->paginate($perPage);
        } else {
            return redirect('/arrival-departure-tracking');
        }
    
        return view('history.tracking', compact('trackings', 'search', 'perPage'));
    }    
}
