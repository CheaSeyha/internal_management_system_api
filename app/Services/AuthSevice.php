<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\AuthRepository;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\json;

class AuthSevice
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
    public function login($loginRequest)
    {
        $credentials = $loginRequest->only('email', 'password');

        // Check if user email exists
        $user = $this->authRepository->checkEmailExists($credentials['email']);
        if (!$user) {
            return $this->responseHelper->fail('Unauthorized User not found', null, 404);
        }

        // Check if password is correct
        if (!$accessToken = Auth::attempt($credentials)) {
            return $this->responseHelper->fail('Unauthorized Email or Password', null, 401);
        }

        // If the user is found and the password is correct, return the access token and user data
        return $this->respondWithToken($accessToken, $user);
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
    public function refreshToken()
    {
        $user = Auth::user();
        return $this->respondWithToken(auth()->refresh(), $user, 'Token refreshed successfully');
    }

    /**
     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();
        return $this->responseHelper->success('User logged out successfully',null, 200);
    }

    protected function respondWithToken($token, $user, $message = 'Login successful')
    {
        return $this->responseHelper->success($message, [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ], 200);
    }
}
