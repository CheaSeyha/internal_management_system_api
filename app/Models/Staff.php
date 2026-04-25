<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $fillable = [
        'staff_id',
        'first_name',
        'last_name',
        'genders',
        'label_id',
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

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'staff_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'staff_id');
    }

    public function rosters()
    {
        return $this->hasMany(Roster::class, 'staff_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $allowedFilters = [
            'department' => fn(Builder $q, array $values) => $q->whereHas(
                'department',
                fn(Builder $departmentQuery) => $departmentQuery->whereIn('department_name', $values)
            ),
            'position' => fn(Builder $q, array $values) => $q->whereHas(
                'position',
                fn(Builder $positionQuery) => $positionQuery->whereIn('position_name', $values)
            ),
            'employment_status' => fn(Builder $q, array $values) => $q->whereIn('status', $values),
        ];

        $searchFilters = [
            'staff_name' => fn(Builder $q, string $value) => $q->where(function (Builder $sub) use ($value) {
                $sub->where('first_name', 'LIKE', "%{$value}%")
                    ->orWhere('last_name', 'LIKE', "%{$value}%")
                    ->orWhere('staff_id', 'LIKE', "%{$value}%");
            }),
        ];

        foreach ($allowedFilters as $filterKey => $applyFilter) {
            $query->when(
                !empty($filters[$filterKey]),
                fn(Builder $builder) => $applyFilter($builder, $filters[$filterKey])
            );
        }

        foreach ($searchFilters as $filterKey => $applyFilter) {
            $query->when(
                !empty($filters[$filterKey]),
                fn(Builder $builder) => $applyFilter($builder, $filters[$filterKey])
            );
        }

        return $query;
    }
}
