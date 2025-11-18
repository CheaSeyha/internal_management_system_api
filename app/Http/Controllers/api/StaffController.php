<?php

namespace App\Http\Controllers\api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    //
    protected $response_helper;
    protected $staffService;
    public function __construct(ResponseHelper $response_helper, StaffService $staff_service)
    {
        $this->response_helper = $response_helper;
        $this->staffService = $staff_service;
    }

    public function addNewStaff(Request $request)
    {
        $validate = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            "phone_number" => "required | string",
            "role_name" => "required | string",
            "position_name" => "required | string",
            "department_name" => "required | string",
            "status" => "required | string",
            "date_of_birth" => "required | date",
            'email' => 'required|email|max:255|unique:staff,email',
            'password' => 'required|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // for createa user profile
        ]);

        try {
            $response = $this->staffService->add_staff($validate);

            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Can not add new staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
