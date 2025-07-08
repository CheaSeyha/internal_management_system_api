<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function dayoff()
    {
        return $this->belongsTo(Dayoff::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function rosters()
    {
        return $this->hasMany(Roster::class);
    }
}
