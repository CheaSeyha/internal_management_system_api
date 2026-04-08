<?php

namespace App\Helper;

class ResponseHelper
{
    /**
     * Return a success response for API.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($message = 'Success', $data = null, $code = 200, $cookies = [])
    {
        $response = response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);

        // Attach cookies if provided
        foreach ($cookies as $cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }

    /**
     * Return a failure response for API.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public static function fail($message = 'Failed', $errors = null, $code = 400, $cookies = [])
    {
        $response = response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);

        foreach ($cookies as $cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }
}
