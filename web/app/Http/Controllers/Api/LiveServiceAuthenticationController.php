<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LiveServiceAuthenticationController extends Controller
{
    private string $hash_algortihm = 'HS256';
    private int $expirationSeconds = 3600;

    private function getJWTKey()
    {
        // Return from env base64 decoded
        return config('app.JWT_SECRET');
    }

    public function generateUserToken()
    {
        $user = auth()->user();

        if (!$user) {
            // Respond with 403
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $payload = [
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->expirationSeconds,
        ];

        return response()->json([
            'token' => JWT::encode($payload, $this->getJWTKey(), $this->hash_algortihm)
        ]);
    }
}
