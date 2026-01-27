<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\PositionRepository;

class PositionService
{
    protected $positionRepository;
    protected $responseHelper;

    public function __construct(PositionRepository $positionRepository, ResponseHelper $responseHelper)
    {
        $this->positionRepository = $positionRepository;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Get all positions.
     */
    public function getAllPositions()
    {
        try {
            $positions = $this->positionRepository->getAllPositions();
            if ($positions) {
                return $this->responseHelper->success('Positions retrieved successfully', $positions, 200);
            }
            return $this->responseHelper->fail('No positions found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while retrieving positions: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Add a new position.
     */
    public function addPosition($data)
    {
        try {
            $res = $this->positionRepository->addPosition($data);
            if ($res) {
                return $this->responseHelper->success('Position created successfully', $res, 200);
            }
            return $this->responseHelper->fail('Failed to create position', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while creating position: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Update an existing position.
     */
    public function updatePosition($id, $data)
    {
        try {
            $res = $this->positionRepository->updatePosition($id, $data);
            if ($res) {
                return $this->responseHelper->success('Position updated successfully', $res, 200);
            }
            return $this->responseHelper->fail('Position not found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while updating position: ' . $th->getMessage(), null, 500);
        }
    }

    /**
     * Delete a position.
     */
    public function deletePosition($id)
    {
        try {
            $res = $this->positionRepository->deletePosition($id);
            if ($res) {
                return $this->responseHelper->success('Position deleted successfully', $res, 200);
            }
            return $this->responseHelper->fail('Position not found', null, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while deleting position: ' . $th->getMessage(), null, 500);
        }
    }
}
