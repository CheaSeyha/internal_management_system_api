<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    protected $table = 'tbl_roster';

    protected $fillable = [
        'staff_id',          // Foreign Key
        'shift_id',          // Foreign Key
        'work_date',
        'shift_id',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}