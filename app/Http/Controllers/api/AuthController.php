<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\AuthService; // Ensure this path is correct based on your project structure
use App\Services\AuthSevice;

class AuthController extends Controller
{

    protected $authService;

    public function __construct(AuthSevice $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $loginResponse = $this->authService->login($request);
            return response()->json($loginResponse->getData(),$loginResponse->getStatusCode());

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    // //
    // public function register(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //     ]);

    //     $user = \App\Models\User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //     ]);

    //     return response()->json(['message' => 'User registered successfully'], 201);
    // }

    // public function login(Request $request)
    // {
    //     try {
    //         //code...
    //         $loginResponse = $this->authService->login($request);
    //         if ($loginResponse instanceof \Illuminate\Http\JsonResponse) {
    //             return $loginResponse; // Return the response from AuthService
    //         }

    //         // If login is successful, respond with token and user data
    //         return $this->respondWithToken($loginResponse['token'], $loginResponse['user']);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //     }
    // }

    // public function user(Request $request)
    // {
    //     return response()->json($request->user());
    // }

    // public function logout()
    // {
    //     auth::logout();
    //     return response()->json(['message' => 'User logged out successfully']);
    // }

    // public function refreshToken()
    // {
    //     $user = Auth::user();
    //     return $this->respondWithToken(Auth::refresh(),$user);
    // }

    // protected function respondWithToken($token,$user)
    // {
    //     return response()->json([
    //         'user'        => $user,
    //         'message'     => 'Token generated successfully',
    //         'access_token' => $token,
    //         'token_type'   => 'bearer',
    //         'expires_in'   => auth()->factory()->getTTL() * 60,
    //     ]);
    // }
}
