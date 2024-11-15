<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\LiveDisplay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Rules\UniqueBinaryTagIdHex;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class LiveDisplayController extends Controller
{
    public function index(Request $request)
    {
        $clients = LiveDisplay::paginate(10);
        $unauthenticatedClients = $this->getUnauthenticatedClients();
        return view("live-display.index", [
            "clients" => $clients,
            "unauthenticatedClients" => $unauthenticatedClients,
            "stats" => $this->getStats(),
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->search;

        if ($search) {
            $clients = LiveDisplay::where(function($query) use ($search) {
                $query->where("label", "like", "%" . $search . "%")
                    ->orWhere("title", "like", "%" . $search . "%")
                    ->orWhere("group_regex_filter", "like", "%" . $search . "%")
                    ->orWhere("class_regex_filter", "like", "%" . $search . "%")
                    ->orWhere("filter_mode", "like", "%" . $search . "%")
                    ->orWhere("is_enabled", "like", "%" . $search . "%");
            })->paginate(10);
        }
        else {
            return redirect('/live-display');
        }

        return view('live-display.index', compact('clients', 'search'));
    }

    public function editor(Request $request, LiveDisplay $live_display){
        return view("live-display.form",
        ["live_display" => $live_display,
        'title'=>'Edit Live Display',
        'button' => 'Edit'
    ]);
    }

    public function update(Request $request, LiveDisplay $liveDisplay)
    {
        $validated = $request->validate([
            "label" => "required|string|max:255",
            "title" => "required|string|max:255",
            "group_regex_filter" => "string|nullable|max:255",
            "class_regex_filter" => "string|nullable|max:255",
            "filter_mode" => "required|in:or,and",
            "is_enabled" => 'required',
        ]);

        $liveDisplay->update($validated);
        return redirect()->route('live-display.index')->with(["success" => "Live display updated successfully."]);
    }

    public function create()
    {
        $clients = $this->getUnauthenticatedClients();

        return view('live-display.unauthenticated',
        ['title' => "Add Live Display",
        'button' => 'Add',
        'clients' => $clients
    ]);
    }

    public function insert(String $liveDisplayString)
    {
        parse_str($liveDisplayString, $liveDisplay);
        $existingLiveDisplays = LiveDisplay::query()->get();
        foreach($existingLiveDisplays as $existingLiveDisplay){
            if($existingLiveDisplay['fingerprint'] == $liveDisplay['unauthenticatedClient']['fingerprintHex']){
                return redirect()->back()->with('error', 'Live display is already authenticated.');
            }
        }
        $authenticatedLiveDisplay = new LiveDisplay;
        $authenticatedLiveDisplay->fingerprint = $liveDisplay['unauthenticatedClient']['fingerprintHex'];
        $authenticatedLiveDisplay->is_enabled = true;
        $authenticatedLiveDisplay->label = $liveDisplay['unauthenticatedClient']['humanReadableIdentifier'];
        $authenticatedLiveDisplay->save();


        return redirect()->back()->with(['success' => 'Live display authenticated successfully.']);
    }

    public function disable(LiveDisplay $liveDisplay)
    {
        $liveDisplay->update([
            'is_enabled' => false,
        ]);

        return redirect()->back()->with(['success'=>'Live display disabled successfully']);
    }

    public function enable(LiveDisplay $liveDisplay)
    {
        $liveDisplay->update([
            'is_enabled' => true,
        ]);

        return redirect()->back()->with(['success'=>'Live display enabled successfully']);
    }


    public function delete(Request $request, LiveDisplay $liveDisplay)
    {
        $liveDisplay->delete();
        return redirect()->back()->with(["success" => "Live display deleted successfully."]);
    }

    public function authenticate(LiveDisplay $liveDisplay)
    {
        
        $authenticatedLiveDisplay = new LiveDisplay;
        $authenticatedLiveDisplay->fingerprint = $liveDisplay['fingerprintHex'];
        $authenticatedLiveDisplay->is_enabled = true;
        $authenticatedLiveDisplay->save();


        return redirect()->back()->with(['success' => 'Live display authenticated successfully.']);
    }

    public static function getUnauthenticatedClients()
    {
        try {
            $response = Http::acceptJson()->get(config("app.LIVE_SERVICE_URL") . '/api/b/ws/unauthenticated-clients');
        } catch (ConnectionException $e) {
            Log::error($e->getMessage());
            return null;
        }

        if ($response->ok()) {
            return $response->json();
        } else {
            Log::error($response->body());
            return null;
        }
    }

    public function getStats()
    {
        try {
            $response = Http::acceptJson()->get(config("app.LIVE_SERVICE_URL") . '/api/b/ws/stats');
        } catch (ConnectionException $e) {
            return null;
        }

        if ($response->ok()) {
            return $response->json();
        } else {
            return null;
        }
    }
}
