<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['room_name', 'building_id'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
