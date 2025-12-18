<?php

namespace App\Http\Controllers\api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\ISPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ISPController extends Controller
{
    protected $ispService;

    public function __construct(ISPService $ispService)
    {
        $this->ispService = $ispService;
    }

    public function getAllISPs()
    {
        try {
            $isps = $this->ispService->getAllISPs();
            return ResponseHelper::success('ISPs retrieved successfully', $isps);
        } catch (\Throwable $th) {
            return ResponseHelper::fail('Failed to fetch ISPs', $th->getMessage(), 500);
        }
    }

    public function addISP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isp_name' => 'required|string|max:100|unique:isps,isp_name',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::fail('Validation Error', $validator->errors(), 400);
        }

        try {
            $isp = $this->ispService->addISP($request->all());
            return ResponseHelper::success('ISP created successfully', $isp, 201);
        } catch (\Throwable $th) {
            return ResponseHelper::fail('Failed to create ISP', $th->getMessage(), 500);
        }
    }

    public function updateISP(Request $request, $isp_id)
    {
        $validator = Validator::make($request->all(), [
            'isp_name' => 'required|string|max:100|unique:isps,isp_name,' . $isp_id,
        ]);

        if ($validator->fails()) {
            return ResponseHelper::fail('Validation Error', $validator->errors(), 400);
        }

        try {
            $isp = $this->ispService->updateISP($isp_id, $request->all());

            if (!$isp) {
                return ResponseHelper::fail('ISP not found', null, 404);
            }

            return ResponseHelper::success('ISP updated successfully', $isp);
        } catch (\Throwable $th) {
            return ResponseHelper::fail('Failed to update ISP', $th->getMessage(), 500);
        }
    }

    public function deleteISP($isp_id)
    {
        try {
            $deleted = $this->ispService->deleteISP($isp_id);

            if (!$deleted) {
                return ResponseHelper::fail('ISP not found', null, 404);
            }

            return ResponseHelper::success('ISP deleted successfully');
        } catch (\Throwable $th) {
            return ResponseHelper::fail('Failed to delete ISP', $th->getMessage(), 500);
        }
    }
}
