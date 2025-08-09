<?php

namespace App\Repository;

use App\Models\Building;
use App\Models\Room;

class BlockRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    // Building CRUD -----------------------------
    public function getAllBuildings()
    {
        $buildings = Building::orderBy('building_name', 'asc')->get();

        return $buildings->isEmpty() ? false : $buildings;

    }

    public function createBuilding($building_name)
    {
        $addBld = Building::create([
            'building_name' => $building_name
        ]);

        return $addBld ?: false;
    }

    public function updateBuilding($building_id, $building_name)
    {


        $building = Building::find($building_id);
        if (!$building) {
            return false;
        }

        $building->building_name = $building_name;
        $building->save();

        return $building;
    }

    public function deleteBuilding($building_id)
    {
        $building = Building::find($building_id);
        if (!$building) {
            return false;
        }

        $building->delete();
        return true;
    }
    // Building CRUD -----------------------------

    // Room CRUD -----------------------------
    public function createRoom($room_name, $building_id)
    {
        $addRoom = Room::create([
            'room_name' => $room_name,
            'building_id' => $building_id,
        ]);

        if (!$addRoom) {
            return false;
        }

        // Get building name from relationship
        $buildingName = $addRoom->building()->value('building_name');

        // Return formatted data as you want
        return [
            'id' => $addRoom->id,
            'building_id' => $addRoom->building_id,
            'building_name' => $buildingName,
            'room' => $addRoom->room_name,
        ];
    }


    public function getAllRooms()
    {
        $rooms = Room::with('building')->get();

        if (!$rooms) {
            return false;
        }

        return $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'building_id' => $room->building_id,
                'building_name' => $room->building ? $room->building->building_name : null,
                'room' => $room->room_name,
            ];
        });
    }

    public function deleteRoom($room_name, $building_id)
    {
        $room = Room::where('room_name', $room_name)
            ->where('building_id', $building_id)
            ->first();

        if (!$room) {
            return false;
        }

        $room->delete();
        return true;
    }
    // Room CRUD -----------------------------
}
