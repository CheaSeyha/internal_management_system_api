<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'department_name'
    ];
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
