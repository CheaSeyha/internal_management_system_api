<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $table = 'shifts';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    public function rosters()
    {
        return $this->hasMany(Roster::class, 'shift_id');
    }
}
