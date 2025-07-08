<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Isp extends Model
{
    public function connectionTypes()
    {
        return $this->hasMany(ConnectionType::class);
    }
}
