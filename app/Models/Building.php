<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    protected $fillable = ['building_name'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    protected $hidden = [
        'updated_at',
        'created_at',
    ];

    // Building.php
    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_building_room')
            ->withPivot('room_id')
            ->withTimestamps();
    }
}
