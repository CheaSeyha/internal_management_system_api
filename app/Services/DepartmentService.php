<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\DepartmentRepository;

class DepartmentService
{
    protected $departmentRepository;
    protected $responseHelper;

    public function __construct(DepartmentRepository $departmentRepository, ResponseHelper $responseHelper)
    {
        $this->departmentRepository = $departmentRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Get all departments.
     */
    public function getAllDepartments()
    {
        try {
            $departments = $this->departmentRepository->getAllDepartments();
            if ($departments) {
                return $this->responseHelper->success('Departments retrieved successfully', $departments, 200);
            }
            return $this->responseHelper->fail('No departments found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while retrieving departments: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Add a new department.
     */
    public function addDepartment($department_name)
    {
        try {
            $res = $this->departmentRepository->addDepartment($department_name);
            if ($res) {
                return $this->responseHelper->success('Department created successfully', $res, 200);
            }
            return $this->responseHelper->fail('Failed to create department', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while creating department: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Update an existing department.
     */
    public function updateDepartment($id, $department_name)
    {
        try {
            $res = $this->departmentRepository->updateDepartment($id, $department_name);
            if ($res) {
                return $this->responseHelper->success('Department updated successfully', $res, 200);
            }
            return $this->responseHelper->fail('Department not found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while updating department: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Delete a department.
     */
    public function deleteDepartment($id)
    {
        try {
            $res = $this->departmentRepository->deleteDepartment($id);
            if ($res) {
                return $this->responseHelper->success('Department deleted successfully', $res, 200);
            }
            return $this->responseHelper->fail('Department not found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while deleting department: ' . $th->getMessage(), null, 500);
        }
    }
}
