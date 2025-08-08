<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\BlockRepository;
use PhpParser\Node\Stmt\TryCatch;

class BlocksService
{
    /**
     * Create a new class instance.
     */
    protected $blockRepository;
    protected $responseHelper;
    public function __construct(BlockRepository $blockRepository, ResponseHelper $responseHelper)
    {
        $this->blockRepository = $blockRepository;
        $this->responseHelper = $responseHelper;
    }


    public function getAllBuildings()
    {
        try {
            $buildings = $this->blockRepository->getAllBuildings();
            if ($buildings) {
                return $this->responseHelper->success('Buildings retrieved successfully', $buildings, 200);
            }
            return $this->responseHelper->fail('No buildings found', null, 404);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->responseHelper->fail('An error occurred while retrieving buildings' . $th->getMessage(), null, 500);
        }
    }

    public function createBuilding($getBuilding_name)
    {
        $building_name = $getBuilding_name->input('building_name');  // fix here
        try {
            $res = $this->blockRepository->createBuilding($building_name);
            if ($res) {
                return $this->responseHelper->success('Building created successfully', $res, 200);
            }
            return $this->responseHelper->fail('Failed to create building', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while creating building: ' . $th->getMessage(), null, 500);
        }
    }

    public function updateBuilding($getBuilding_name, $building_id)
    {
        try {
            $res = $this->blockRepository->updateBuilding($building_id, $getBuilding_name);
            if ($res) {
                return $this->responseHelper->success('Building updated successfully', $res, 200);
            }
            return $this->responseHelper->fail('Building Not Found', $res, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while creating Building: ' . $th->getMessage(), null, 500);
        }
    }

    public function deleteBuilding($building_id)
    {
        try {
            $res = $this->blockRepository->deleteBuilding($building_id);
            if ($res) {
                return $this->responseHelper->success('Building deleted successfully', $res, 200);
            }
            return $this->responseHelper->fail('Building Not Exist', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while deleting Building: ' . $th->getMessage(), null, 500);
        }
    }

    public function createRoom($getRoom_name, $building_id)
    {

        try {
            $res = $this->blockRepository->createRoom($getRoom_name, $building_id);
            if ($res) {
                return $this->responseHelper->success('Room created successfully', $res, 200);
            }
            return $this->responseHelper->fail('Failed to create Room', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while creating Room: ' . $th->getMessage(), null, 500);
        }
    }

    public function getAllRooms()
    {
        try {
            $res = $this->blockRepository->getAllRooms();
            if ($res) {
                return $this->responseHelper->success('Room retrive successfully', $res, 200);
            }
            return $this->responseHelper->fail('Failed to retrive Room', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while retrive Room: ' . $th->getMessage(), null, 500);
        }
    }

    public function deleteRoom($room_name, $building_id)
    {
        try {
            $res = $this->blockRepository->deleteRoom($room_name, $building_id);
            if ($res) {
                return $this->responseHelper->success('Room deleted successfully', $res, 200);
            }
            return $this->responseHelper->fail('Room not found or has been deleted', null, 400);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('An error occurred while deleted Room: ' . $th->getMessage(), null, 500);
        }
    }
}
