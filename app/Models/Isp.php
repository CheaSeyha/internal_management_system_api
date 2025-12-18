<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Isp extends Model
{
    protected $fillable = [
        'isp_name',
    ];

    public function connectionTypes()
    {
        return $this->hasMany(ConnectionType::class);
    }

    public function cards(){
        return $this->hasMany(Card::class,'isp_id');
    }


}
