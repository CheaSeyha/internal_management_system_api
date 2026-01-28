<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function getAllDepartments()
    {
        try {
            $response = $this->departmentService->getAllDepartments();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch departments.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addDepartment(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|unique:departments,department_name',
        ]);

        try {
            $response = $this->departmentService->addDepartment($validated['department_name']);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateDepartment(Request $request, $department_id)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|unique:departments,department_name,' . $department_id,
        ]);

        try {
            $response = $this->departmentService->updateDepartment($department_id, $validated['department_name']);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDepartment($department_id)
    {
        try {
            $response = $this->departmentService->deleteDepartment($department_id);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
