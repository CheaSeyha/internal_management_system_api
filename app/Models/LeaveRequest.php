<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $table = 'leave_requests';

    protected $fillable = [
        'id',          // Foreign Key
        'leave_type_id',     // Foreign Key
        'start_date',
        'end_date',
        'days',
        'status',
        'reason',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
