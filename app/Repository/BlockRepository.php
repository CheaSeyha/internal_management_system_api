<?php

namespace App\Repository;

use App\Models\Building;
use App\Models\CardBuildingRoom;
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
        $buildings = Building::with('rooms')
            ->orderBy('building_name', 'asc') // Sort A-Z
            ->get();

        return $buildings->map(function ($building) {
            // Count distinct card IDs linked to this building
            $cardCount = CardBuildingRoom::where('building_id', $building->id)
                ->distinct('card_id') // ensure each card is counted once
                ->count('card_id');

            return [
                'building' => $building->building_name,
                'room'     => $building->rooms->pluck('room_name')->toArray(),
                'count'    => $cardCount, // total cards for this building
            ];
        });
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
    public function createRoom($room_name, $building_name)
    {
        // 🔹 Find building by name
        $building = Building::where('building_name', $building_name)->first();

        if (!$building) {
            return false; // or throw exception / return custom response
        }

        $addRoom = Room::create([
            'room_name'   => $room_name,
            'building_id' => $building->id, // use ID internally
        ]);

        if (!$addRoom) {
            return false;
        }

        return [
            'id'            => $addRoom->id,
            'building_id'   => $addRoom->building_id,
            'building_name' => $building->building_name, // from DB
            'room'          => $addRoom->room_name,
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
