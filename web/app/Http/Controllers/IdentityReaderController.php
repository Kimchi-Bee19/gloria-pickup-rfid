<?php

namespace App\Http\Controllers;

use App\Models\IdentityReader;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\Builder\Identity;

class IdentityReaderController extends Controller
{
    public function index()
    {
        return view("identity-reader.index", [
            "data" => IdentityReader::paginate(10),
            "status" => $this->getStatus(),
            "setup" => $this->getSetup(),
            "clientTypeMap" => [
                0 => "Student RFID",
                1 => "Vehicle RFID",
            ]
        ]);
    }

    public function update(Request $request, IdentityReader $identityReader)
    {
        $validated = $request->validate([
            "label" => "string|max:255|unique:identity_readers,label"
        ]);

        $identityReader->update($validated);
        return redirect()->back();
    }

    public function configure(Request $request)
    {
        $validated = $request->validate([
            "clientid" => "string|max:255",
            "label" => "string|max:255|unique:identity_readers,label",
            "username" => "string|max:255|unique:identity_readers,username",
            "type" => "in:student_rfid,vehicle_rfid"
        ]);

        // Generate a random 64 character password
        $password = Str::random(64);
        $identityReader = new IdentityReader($validated);
        $identityReader["password_hash"] = $password;
        $identityReader->save();

        // Attempt to configure the actual client
        $response = $this->configureClient($validated['clientid'], $validated['username'], $password);
        if (!$response) {
            return redirect()->back()->with([
                "error" => "Unable to configure client. Check the logs for more information."
            ]);
        }

        if($response['result'] === "timeout") {
            return redirect()->back()->with([
                "error" => "Unable to configure client. Client did not connect within the timeout period."
            ]);
        }

        return redirect()->back()->with([
            "success" => "Client configured successfully."
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            "label" => "string|max:255|unique:identity_readers,label",
            "username" => "string|max:255|unique:identity_readers,username",
            "password" => "string|max:255",
            "type" => "in:student_rfid,vehicle_rfid,superuser,external"
        ]);

        $identityReader = IdentityReader::create($validated);
        $identityReader->password_hash = $validated['password'];

        $identityReader->save();
        return redirect()->back();
    }

    public function delete(Request $request, IdentityReader $identityReader)
    {
        $identityReader->delete();
        return redirect()->back()->with(["success" => "Identity reader deleted successfully."]);
    }

    private function getSetup()
    {
        try {
            $response = Http::acceptJson()->get(config("app.LIVE_SERVICE_URL") . '/api/b/identity-readers/poll-setup');
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

    private function getStatus()
    {
        try {
            $response = Http::acceptJson()->get(config("app.LIVE_SERVICE_URL") . '/api/b/identity-readers/poll-status');
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

    private function configureClient(string $clientId, string $username, string $password)
    {
        try {
            $response = Http::acceptJson()->post(config("app.LIVE_SERVICE_URL") . '/api/b/identity-readers/configure', [
                'clientid' => $clientId,
                'username' => $username,
                'password' => $password
            ]);
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
}
