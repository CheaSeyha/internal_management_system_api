<?php

namespace App\Http\Controllers\api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            "status" => "nullable | string",
            "date_of_birth" => "required | date",
            'email' => 'required|email|max:255|unique:staff,email',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // for createa user profile
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

    public function getAllStaff()
    {
        try {
            $response = $this->staffService->getAllStaff();

            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Can not add new staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getProfileImage($id)
    {
        $staff = Staff::findOrFail($id);

        if (!$staff->profile_picture) {
            return response()->json(['message' => 'No profile picture'], 404);
        }

        $path = $staff->profile_picture;

        if (!Storage::disk('private')->exists($path)) {
            return response()->json(['message' => 'File missing'], 404);
        }

        return Storage::disk('private')->response($path);
    }
}
