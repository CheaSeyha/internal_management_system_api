<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
        $credentials = $req->only('email', 'password', 'remember_me');

        $response = Http::asForm()->post(env('APP_DEV_URL') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'username' => $credentials['email'],
            'password' => $credentials['password'],
            'scope' => '',
        ]);

        if ($response->failed()) {
            return ResponseHelper::fail('Invalid credentials', null, 401);
        }

        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        $userResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get(env('APP_DEV_URL') . '/api/v1/user');

        $user = $userResponse->successful() ? $userResponse->json('data') : null;

        $data = [
            "user" => $user,
            "access_token" => $response->json('access_token'),
            "token_type" => $response->json('token_type'),
            "expires_in" => $response->json('expires_in'),
        ];

        $rememberMeTtl = !empty($credentials['remember_me']) ? 60 * 24 * 30 : 0;
        $refreshTokenCookie = cookie(
            'refresh_token',
            $refreshToken,
            $rememberMeTtl,
            '/',
            null,
            false,   // secure (HTTPS)
            true,   // httpOnly
            false,
            'lax'
        );

        $rememberMeCookieValue = !empty($credentials['remember_me']) ? '1' : '0';
        $rememberMeCookie = cookie(
            'remember_me',
            $rememberMeCookieValue,
            $rememberMeTtl,
            '/',
            null,
            false,   // secure (HTTPS)
            true,   // httpOnly
            false,
            'lax'
        );

        return ResponseHelper::success(
            'Login successful',
            $data,
            200,
            [$refreshTokenCookie, $rememberMeCookie]
        )->withCookie($refreshTokenCookie)->withCookie($rememberMeCookie);
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
    public function refreshToken($request)
    {
        $refreshToken = $request->cookie('refresh_token');

        $response = Http::asForm()->post(env('APP_DEV_URL') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'), // Required for confidential clients only...
            'scope' => '',
        ]);


        if ($response->failed()) {
            return $this->responseHelper->fail('Invalid credentials', null, 401);
        }

        if ($response->failed()) {
            return ResponseHelper::fail('Invalid credentials', null, 401);
        }

        $data = [
            "access_token" => $response->json('access_token'),
            "token_type" => $response->json('token_type'),
            "expires_in" => $response->json('expires_in'),
        ];

        $rememberMeValue = $request->cookie('remember_me');
        $rememberMeTtl = $rememberMeValue === '1' ? 60 * 24 * 30 : 0;

        $refreshTokenCookie = cookie(
            'refresh_token',
            $response->json('refresh_token'),
            $rememberMeTtl,
            '/',
            null,
            false,   // secure (HTTPS)
            true,   // httpOnly
            false,
            'lax'
        );

        $rememberMeCookie = cookie(
            'remember_me',
            $rememberMeValue === '1' ? '1' : '0',
            $rememberMeTtl,
            '/',
            null,
            false,   // secure (HTTPS)
            true,   // httpOnly
            false,
            'lax'
        );

        return ResponseHelper::success(
            'Refresh token success',
            $data,
            200,
            [$refreshTokenCookie, $rememberMeCookie]
        )->withCookie($refreshTokenCookie)->withCookie($rememberMeCookie);
    }

    /**
     * Handle user logout.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $token = Auth::user()->token();

        // Revoke access token
        $token->revoke();

        // Revoke associated refresh token (if exists)
        $token->refreshToken?->revoke();

        // Expire both cookies by setting TTL to -1
        $refreshTokenCookie = cookie('refresh_token', '', -1, '/', null, false, true, false, 'lax');
        $rememberMeCookie = cookie('remember_me', '', -1, '/', null, false, true, false, 'lax');

        // Return response with cookie removed
        return $this->responseHelper->success('User logged out successfully', null, 200, [$refreshTokenCookie, $rememberMeCookie])
            ->withCookie($refreshTokenCookie)
            ->withCookie($rememberMeCookie);
    }
}
