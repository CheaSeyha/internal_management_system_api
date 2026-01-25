<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'position_name',
        'department_id'
    ];
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
