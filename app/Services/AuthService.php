<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\User;
use App\Repository\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    protected $authRepository;
    protected $responseHelper;

    public function __construct(AuthRepository $authRepository, ResponseHelper $responseHelper)
    {
        $this->authRepository = $authRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Handle user login.
     */
    public function login($req)
    {
        $credentials = $req->only('email', 'password', 'remember_me');

        $checkAccountStatus = User::where('email', $credentials['email'])->first();

        if ($checkAccountStatus->account_status !== 'active') {
            return $this->responseHelper->fail('Account is not active', null, 401);
        }

        $response = Http::asForm()->post(env('APP_DEV_URL') . '/oauth/token', [
            'grant_type'    => 'password',
            'client_id'     => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'username'      => $credentials['email'],
            'password'      => $credentials['password'],
            'scope'         => '',
        ]);

        if ($response->failed()) {
            return ResponseHelper::fail('Invalid credentials', null, 401);
        }

        $accessToken  = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        // ✅ Get user directly from DB — no internal HTTP call needed
        $user = User::where('email', $credentials['email'])
            ->select('id', 'name', 'email', 'staff_id', 'role_id', 'profile_image')
            ->first();

        $data = [
            'user'         => $user,
            'access_token' => $accessToken,
            'token_type'   => $response->json('token_type'),
            'expires_in'   => $response->json('expires_in'),
        ];

        $rememberMeTtl = !empty($credentials['remember_me']) ? 60 * 24 * 30 : 0;

        $refreshTokenCookie = cookie('refresh_token', $refreshToken, $rememberMeTtl, '/', null, false, true, false, 'lax');
        $rememberMeCookie   = cookie('remember_me', !empty($credentials['remember_me']) ? '1' : '0', $rememberMeTtl, '/', null, false, true, false, 'lax');

        return ResponseHelper::success('Login successful', $data, 200)
            ->withCookie($refreshTokenCookie)
            ->withCookie($rememberMeCookie);
    }

    /**
     * Handle user registration.
     */
    public function register($registerRequest)
    {
        $user = $this->authRepository->createUser($registerRequest);

        if (!$user) {
            return $this->responseHelper->fail('User registration failed', null, 500);
        }

        return $this->responseHelper->success('User registered successfully', $user, 201);
    }

    /**
     * Get the authenticated user.
     */
    public function getUser($request)
    {
        // ✅ Use $request->user() — works correctly with auth:api middleware
        $user = $request->user();

        if (!$user) {
            return $this->responseHelper->fail('Unauthorized, user not found', null, 401);
        }

        return $this->responseHelper->success('User retrieved successfully', $user, 200);
    }

    /**
     * Refresh access token using refresh_token cookie.
     */
    public function refreshToken($request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return $this->responseHelper->fail('Refresh token not found', null, 401);
        }

        $response = Http::asForm()->post(env('APP_DEV_URL') . '/oauth/token', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id'     => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'scope'         => '',
        ]);

        if (isset($request->user()->email)) {
            $checkAccountStatus = User::where('email', $request->user()->email)->first();

            if ($checkAccountStatus->account_status !== 'active') {
                return $this->responseHelper->fail('Account is not active', null, 401);
            }
        }

        if ($response->failed()) {
            return $this->responseHelper->fail('Invalid or expired refresh token', null, 401);
        }

        $data = [
            'access_token' => $response->json('access_token'),
            'token_type'   => $response->json('token_type'),
            'expires_in'   => $response->json('expires_in'),
        ];

        $rememberMeValue = $request->cookie('remember_me');
        $rememberMeTtl   = $rememberMeValue === '1' ? 60 * 24 * 30 : 0;

        $refreshTokenCookie = cookie('refresh_token', $response->json('refresh_token'), $rememberMeTtl, '/', null, false, true, false, 'lax');
        $rememberMeCookie   = cookie('remember_me', $rememberMeValue === '1' ? '1' : '0', $rememberMeTtl, '/', null, false, true, false, 'lax');

        return ResponseHelper::success('Token refreshed successfully', $data, 200)
            ->withCookie($refreshTokenCookie)
            ->withCookie($rememberMeCookie);
    }

    /**
     * Handle user logout.
     */
    public function logout($request)
    {
        // ✅ Use $request->user() with api guard — NOT Auth::user()
        $user = $request->user();

        if (!$user) {
            return $this->responseHelper->fail('Unauthorized, user not found', null, 401);
        }

        $token = $user->token();

        // Revoke access token
        $token->revoke();

        // Revoke refresh token if exists
        if ($token->refreshToken) {
            $token->refreshToken->revoke();
        }

        // Expire cookies
        $refreshTokenCookie = cookie('refresh_token', '', -1, '/', null, false, true, false, 'lax');
        $rememberMeCookie   = cookie('remember_me',   '', -1, '/', null, false, true, false, 'lax');

        return $this->responseHelper->success('Logged out successfully', null, 200)
            ->withCookie($refreshTokenCookie)
            ->withCookie($rememberMeCookie);
    }


    public function userImage($user_id)
    {
        try {
            $user = User::find($user_id);
            if (!$user) {
                return $this->responseHelper->fail('User not found', null, 404);
            }

            $imagePath = $user->profile_image;
            if (!$imagePath) {
                return $this->responseHelper->fail('User image not found', null, 404);
            }

            $image = Storage::disk('private')->get($imagePath);
            $mimeType = Storage::disk('private')->mimeType($imagePath);

            return response($image, 200)->header('Content-Type', $mimeType);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('Failed to fetch user image', 500);
        }
    }
}
