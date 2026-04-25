<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id');
    }
}