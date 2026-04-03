<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\AuthRepository;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;

use function Pest\Laravel\json;

class AuthService
{
    protected $authRepository;
    protected $responseHelper;

    /**
     * Create a new class instance.
     */
    public function __construct(AuthRepository $authRepository, ResponseHelper $responseHelper)
    {
        //
        $this->authRepository = $authRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle user login.
     *
     * @param \Illuminate\Http\Request $loginRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function login($req)
    {
        // Make sure you get only email & password
        $credentials = $req->only('email', 'password');

        // Request token from Passport password grant
        $response = Http::asForm()->post('http://127.0.0.1:8001/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'username' => $credentials['email'],
            'password' => $credentials['password'],
            'scope' => '',
        ]);

        if ($response->failed()) {
            return $this->responseHelper->fail('Invalid credentials', null, 401);
        }

        // Decode the response JSON
        $data = $response->json();

        return $this->responseHelper->success('Login successful', $data, 200);
    }

    /**
     * Handle user registration.
     *
     * @param \Illuminate\Http\Request $registerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function register($registerRequest)
    {
        $user = $this->authRepository->createUser($registerRequest);
        if (!$user) {
            return $this->responseHelper->fail('User registration failed', null, 500);
        }

        // If the user is created successfully, return the user data and access token

        return $this->responseHelper->success('User registered successfully', $user, 201);
    }

    /**
     * Get the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->responseHelper->fail('Unauthorized User not found', null, 404);
        }

        return $this->responseHelper->success('User retrieved successfully', $user, 200);
    }

    /**
     * Get Refresh Token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken($refreshToken)
    {
        $response = Http::asForm()->post('http://127.0.0.1:8001/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'), // Required for confidential clients only...
            'scope' => '',
        ]);


        if ($response->failed()) {
            return $this->responseHelper->fail('Invalid credentials', null, 401);
        }

        // Decode the response JSON
        $data = $response->json();

        return $this->responseHelper->success('User retrieved successfully', $data, 200);
    }

    /**
     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $token = Auth::user()->token();

        $token->revoke();
        $token->refreshToken?->revoke();

        return $this->responseHelper->success('User logged out successfully', null, 200);
    }

    protected function respondWithToken($token, $user, $message = 'Login successful')
    {
        // return $this->responseHelper->success($message, [
        //     'user' => $user,
        //     'access_token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => auth()->factory()->getTTL() * 60,
        // ], 200);
    }
}
