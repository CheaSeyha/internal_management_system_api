<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenReqeust;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request)
    {
        try {
            return $this->authService->login($request);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request)
    {
        try {
            $response = $this->authService->register($request);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        try {
            $response = $this->authService->getUser($request);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh access token.
     */
    public function refreshToken(RefreshTokenReqeust $request)
    {
        try {
            return $this->authService->refreshToken($request);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request) // ✅ Added Request $request
    {
        try {
            $response = $this->authService->logout($request); // ✅ Pass $request
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error'   => $th->getMessage(),
            ], 500);
        }
    }


    public function userImage($user_id)
    {
        try {
            $response = $this->authService->userImage($user_id);
            return $response;
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
