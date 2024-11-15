<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IdentityReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\MockObject\Builder\Identity;

class MQTTAuthenticationController extends Controller
{
    public function authenticate(Request $request)
    {
        // Dirty check for IP address
        $ipAddress = \IPLib\Factory::parseAddressString($request->ip());
        $ranges = array_map(fn($v) => \IPLib\Factory::parseRangeString($v), config('app.MQTT_AUTH_IP'));

        $ipInRange = false;
        for ($i = 0; $i < count($ranges); $i++) {
            if ($ranges[$i]->contains($ipAddress)) {
                $ipInRange = true;
                break;
            }
        }

        if (!$ipInRange) {
            return response()->json([
                'result' => 'deny',
                'errors' => ['IP address not allowed']
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:256',
            'password' => 'required|string|max:256',
            'clientid' => 'required|string|max:256',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => 'deny',
                'errors' => $validator->errors()->all()
            ]);
        }

        $validated = $validator->validated();

        // Separate hardcoded setup flow from normal authentication flow
        if ($validated['username'] === 'setup') {
            // Test password against hardcoded setup key
            if ($validated['password'] === config('app.MQTT_SETUP_KEY')) {
                // The passwords match...
                $authResponse = [
                    'result' => 'allow',
                    'expire_at' => time() + 300, // 5 minutes
                    'acl' => [
                        [
                            "permission" => 'allow',
                            "action" => 'publish',
                            "topic" => 'setup/${clientid}/init',
                        ],
                        [
                            "permission" => 'allow',
                            "action" => 'subscribe',
                            "topic" => 'setup/${clientid}/configure',
                        ],
                        // Implicit deny
                        [
                            'permission' => 'deny',
                            'action' => 'all',
                            'topic' => '#'
                        ]
                    ]
                ];

                return response()->json($authResponse);
            }
        } elseif ($validated['username'] === 'liveservice') {
            // Test password against hardcoded live service password
            if ($validated['password'] === config('app.MQTT_LIVE_SERVICE_PASSWORD')) {
                // The passwords match...
                $authResponse = [
                    'result' => 'allow'
                ];

                return response()->json($authResponse);
            }
        } else {
            // Check against the model
            $client = IdentityReader::where('username', $validated['username'])->first();

            if ($client) {
                // Username and clientid must match
                if ($validated['username'] !== $validated['clientid'])response()->json([
                    'result' => 'deny',
                    'errors' => ['username and clientid must match']
                ]);

                // Test credentials
                $hashedPassword = $client->password_hash;
                if (Hash::check($validated['password'], $hashedPassword)) {
                    // The passwords match...
                    $client->update(["last_login" => time()]);

                    $authResponse = [
                        'result' => 'allow',
                    ];

                    if ($client->type === 'superuser') {
                        $authResponse['is_superuser'] = true;
                    }

                    // Compute ACL values that this client will have
                    // If type is 'student_rfid' 'vehicle_rfid', apply ACL
                    if (in_array($client->type, ['student_rfid', 'vehicle_rfid'])) {
                        $authResponse['client_attrs'] = [
                            'reader_event_topic' => $client->type === 'student_rfid' ? 'student_departure' : 'vehicle_arrival'
                        ];

                        $authResponse['acl'] = [
                            [
                                "permission" => 'allow',
                                "action" => 'all',
                                "topic" => 'dev/readers/${username}/+'
                            ],
                            [
                                "permission" => 'allow',
                                "action" => 'publish',
                                "topic" => 'events/${client_attrs.reader_event_topic}'
                            ],
                            // Implicit deny
                            [
                                'permission' => 'deny',
                                'action' => 'all',
                                'topic' => '#'
                            ]
                        ];
                    }

                    // Update the last_login
                    $client->last_login = now();
                    $client->save();

                    return response()->json($authResponse);
                }
            }
        }

        return response()->json([
            'result' => 'deny',
            'errors' => ['Invalid username or password']
        ]);
    }
}
