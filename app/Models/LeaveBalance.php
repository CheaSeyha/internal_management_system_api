<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 'leave_balance';

    protected $fillable = [
        'staff_id',          // Foreign Key
        'leave_type_id',     // Foreign Key
        'total_days',           // Current balance
        'used_days',              // Days used
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}