<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected $positionService;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    public function getAllPositions()
    {
        try {
            $response = $this->positionService->getAllPositions();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch positions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPosition(Request $request)
    {
        $validated = $request->validate([
            'position_name'   => 'required|string|unique:positions,position_name',
            'department_name' => 'required|exists:departments,department_name',
        ]);

        try {
            $response = $this->positionService->addPosition($validated);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create position.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePosition(Request $request, $position_id)
    {
        $validated = $request->validate([
            'position_name'   => 'sometimes|required|string|unique:positions,position_name,' . $position_id,
            'department_name' => 'sometimes|required|exists:departments,department_name',
        ]);

        try {
            $response = $this->positionService->updatePosition($position_id, $validated);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update position.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePosition($position_id)
    {
        try {
            $response = $this->positionService->deletePosition($position_id);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete position.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
