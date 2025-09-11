<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardBuildingRoom extends Model
{
    protected $table = 'card_building_room';

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
