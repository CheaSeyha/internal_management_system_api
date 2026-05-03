<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    protected $fillable = [
        'id',          // Foreign Key
        'staff_id',          // Foreign Key
        'shift_id',          // Foreign Key
        'work_date',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
