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
}
