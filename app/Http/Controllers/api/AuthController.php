<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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



    /**     * Handle user login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $loginResponse = $this->authService->login($request);
            return response()->json($loginResponse->getData(), $loginResponse->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**     * Register a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {
            $registerResponse = $this->authService->register($request);
            return response()->json($registerResponse->getData(), $registerResponse->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**     * Get the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        try {
            $getUser = $this->authService->getUser($request);
            return response()->json($getUser->getData());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    /**     * Get Refresh Token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken()
    {
        try {
            $getRefreshToken = $this->authService->refreshToken();
            return response()->json($getRefreshToken->getData());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get refresh token',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    /**     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            $logout = $this->authService->logout();
            return response()->json($logout->getData());
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get refresh token',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    
}
