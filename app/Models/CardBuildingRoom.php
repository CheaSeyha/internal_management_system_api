<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardBuildingRoom extends Model
{
    protected $table = 'card_building_room';
    protected $fillable = ['card_id', 'building_id', 'room_id'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }


    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
