<?php

namespace App\Services;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\json;

class AuthSevice
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function login($loginRequest)
    {
        $credentials = $loginRequest->only('email', 'password');

        // Check if user email exists
        $user = \App\Models\User::where('email', $credentials['email'])->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'status' => 404,
            ], 404);
        }

        // Check if password is correct
        if (!$accessToken = Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Email or Password',
                'status' => 401,
            ], 401);
        }

        // If the user is found and the password is correct, return the access token and user data
        return $this->respondWithToken($accessToken, $user);

    }


    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'success'      => true,
            'message'      => 'Login successful',
            'status'       => 200,
            'data'         => [
                'user'        => $user,
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth()->factory()->getTTL() * 60,
            ]
        ]);
    }
}
