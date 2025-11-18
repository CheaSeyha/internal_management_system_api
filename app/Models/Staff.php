<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'position_id',
        'department_id',
        'status',
        'date_of_joining',
        'date_of_birth',
        'profile_picture',
    ];

    protected $hidden = [
        'position_id',
        'department_id',
    ];
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
