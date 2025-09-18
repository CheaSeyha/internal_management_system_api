<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlocksRequest;
use App\Services\BlocksService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    protected $blocks_service;

    public function __construct(BlocksService $blocks_service)
    {
        $this->blocks_service = $blocks_service;
    }

    // Building CRUD -----------------------------

    public function createBuilding(BlocksRequest $request)
    {
        try {
            $response = $this->blocks_service->createBuilding($request);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create building.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBuilding(Request $request, $building_id)
    {
        $validated = $request->validate([
            'building_name' => 'required|string',
        ]);

        try {
            $response = $this->blocks_service->updateBuilding($validated['building_name'], $building_id);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update building.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteBuilding($building_id)
    {
        try {
            $response = $this->blocks_service->deleteBuilding($building_id);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete building.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllBuildings()
    {
        try {
            $response = $this->blocks_service->getAllBuildings();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch buildings.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Room CRUD -----------------------------

    public function getAllRooms()
    {
        try {
            $response = $this->blocks_service->getAllRooms();
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rooms.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createRoom(Request $request)
    {
        $validated = $request->validate([
            'room_name' => [
                'required',
                'string',
                Rule::unique('rooms')->where(fn($query) => $query->where('building_id', $request->building_id)),
            ],
            'building_name' => 'required|exists:buildings,building_name',
        ]);

        try {
            $response = $this->blocks_service->createRoom($validated['room_name'], $validated['building_name']);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteRoom($room_name, $building_id)
    {
        try {
            $response = $this->blocks_service->deleteRoom($room_name, $building_id);
            return response()->json($response->getData(), $response->getStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
