<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\RosterRepository;
use Illuminate\Support\Facades\DB;

class RosterService
{
    /**
     * Create a new class instance.
     */
    protected RosterRepository $rosterRepository;
    protected ResponseHelper $responseHelper;

    public function __construct(RosterRepository $rosterRepository, ResponseHelper $responseHelper)
    {
        $this->rosterRepository = $rosterRepository;
        $this->responseHelper = $responseHelper;
    }

    public function createOrUpdateRoster(array $data)
    {
        $user = auth()->user();

        $isSuperAdmin = $user->role_id == 1;
        $isAdmin = $user->role_id == 2;

        // Authorization Check
        if (!$isSuperAdmin) {
            foreach ($data['staff_roster'] as $staffItem) {

                $staffId = $staffItem['staff_id'];

                if ($isAdmin) {
                    // Check department
                    $targetStaff = DB::table('staff')->where('id', $staffId)->first();

                    if (
                        !$targetStaff ||
                        $targetStaff->department_id != $user->staff->department_id
                    ) {
                        return $this->responseHelper->fail(
                            'Unauthorized. You can only update rosters for staff in your department.',
                            null,
                            403
                        );
                    }
                } else {
                    // Regular user → only own roster
                    if ($staffId != $user->staff->id) {
                        return $this->responseHelper->fail(
                            'Unauthorized. You can only update your own roster.',
                            null,
                            403
                        );
                    }
                }
            }
        }

        $shifts = DB::table('shifts')->pluck('id', 'name');

        return DB::transaction(function () use ($data, $shifts) {

            try {
                $staffRosterResults = [];

                foreach ($data['staff_roster'] as $staffItem) {

                    $staffId = $staffItem['staff_id'];
                    $rosterResults = [];

                    foreach ($staffItem['roster'] as $item) {

                        $workDate = $item['date'];
                        $shiftName = $item['shift_name'];
                        $shiftId = $shifts[$shiftName] ?? null;

                        if (!$shiftId) {
                            throw new \Exception("Shift '{$shiftName}' not found.");
                        }

                        $roster = $this->rosterRepository->updateOrCreate(
                            [
                                'staff_id' => $staffId,
                                'work_date' => $workDate
                            ],
                            [
                                'shift_id' => $shiftId
                            ]
                        );

                        $rosterResults[] = [
                            'date' => $workDate,
                            'shift_name' => $shiftName,
                            'action' => $roster->wasRecentlyCreated ? 'create' : 'update',
                        ];
                    }

                    $staffRosterResults[] = [
                        'staff_id' => (string) $staffId,
                        'roster' => $rosterResults
                    ];

                    // Sync OFF day leave balance for this staff and this month
                    $this->syncOffDayBalance($staffId, $data['month'], $data['year']);
                }

                return $this->responseHelper->success(
                    'Roster created or updated successfully',
                    ['staff_roster' => $staffRosterResults],
                    200
                );
            } catch (\Throwable $th) {
                return $this->responseHelper->fail(
                    'Failed to create or update roster',
                    $th->getMessage(),
                    500
                );
            }
        });
    }

    public function getAllRoster($month, $year)
    {
        try {
            $user = auth()->user();
            $isSuperAdmin = $user->role_id == 1;

            // Get department strictly from authenticated user
            $departmentId = $user->staff->department_id ?? null;

            if (!$isSuperAdmin && !$departmentId) {
                return $this->responseHelper->fail(
                    'Unauthorized. User has no department assigned.',
                    null,
                    400
                );
            }

            // Default month/year
            $year = $year ?: (int)date('Y');
            $month = $month ?: (int)date('m');

            // Fetch ALL staff in the department (or all if superadmin)
            $staffQuery = \App\Models\Staff::with([
                'department',
                'position',
                'user.role',
                'leaveBalances.leaveType',
                'rosters' => function ($query) use ($month, $year) {
                    $query->whereYear('work_date', $year)
                        ->whereMonth('work_date', $month)
                        ->with('shift');
                }
            ]);

            if (!$isSuperAdmin) {
                $staffQuery->where('department_id', $departmentId);
            }

            $allStaff = $staffQuery->get();

            $data = $allStaff
                // group by department first
                ->groupBy(function ($item) {
                    return $item->department->id ?? 0;
                })
                ->map(function ($departmentStaff, $deptId) use ($month, $year) {

                    $first = $departmentStaff->first();
                    $department = $first->department ?? null;

                    return [
                        'department_id' => $department->id ?? null,
                        'department_name' => $department->department_name ?? 'Unknown',

                        // staff inside department
                        'staffs' => $departmentStaff
                            ->map(function ($staff) use ($month, $year) {

                                $user = $staff->user ?? null;
                                $staffRosters = $staff->rosters;

                                // Determine days in month
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                                $rostersByDay = $staffRosters->keyBy(fn($r) => (int)$r->work_date->format('d'));

                                $shiftData = [];
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $roster = $rostersByDay->get($day);
                                    if (!$roster) {
                                        $shiftData[] = null; // Default to null if no roster in DB
                                        continue;
                                    }

                                    $shiftName = $roster->shift->name ?? 'OFF';
                                    if (strtoupper($shiftName) === 'OFF') {
                                        $shiftData[] = 'OFF';
                                    } elseif (preg_match('/\((\d{2}):/', $shiftName, $matches)) {
                                        $shiftData[] = (string) intval($matches[1]);
                                    } else {
                                        $shiftData[] = $shiftName;
                                    }
                                }

                                // Get all Leave Balances dynamically
                                $leaveBalances = $staff->leaveBalances->mapWithKeys(function ($balance) {
                                    $typeName = strtolower(str_replace(' ', '_', $balance->leaveType->name));
                                    return [
                                        $typeName => [
                                            'total' => (int)$balance->total_days,
                                            'used' => (int)$balance->used_days,
                                            'remaining' => (int)($balance->total_days - $balance->used_days),
                                        ]
                                    ];
                                })->toArray();

                                // --- DYNAMIC OFF DAY BALANCE CALCULATION (Snapshot for the viewed month) ---
                                $offShiftId = DB::table('shifts')->where('name', 'OFF')->value('id');
                                $offLeaveTypeId = DB::table('leave_types')->where('name', 'LIKE', '%OFF%')->value('id');

                                $joiningDate = $staff->date_of_joining ? \Carbon\Carbon::parse($staff->date_of_joining) : null;
                                $rosterMonthDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

                                if ($joiningDate && $offShiftId && $offLeaveTypeId) {
                                    // 1. Accrued up to the end of the viewed month
                                    if ($joiningDate->greaterThan($rosterMonthDate)) {
                                        $accruedUpToMonth = 0;
                                    } else {
                                        $monthsDiff = $joiningDate->diffInMonths($rosterMonthDate) + 1;
                                        $accruedUpToMonth = $monthsDiff * 4;
                                    }

                                    // 2. Used up to the end of the viewed month
                                    $usedUpToMonth = DB::table('rosters')
                                        ->where('staff_id', $staff->id)
                                        ->where('shift_id', $offShiftId)
                                        ->where('work_date', '<=', $rosterMonthDate->format('Y-m-d'))
                                        ->count();

                                    // 3. Inject/Override the "off_day" balance for this month's view
                                    $leaveBalances['off_day'] = [
                                        'total' => (int)$accruedUpToMonth,
                                        'used' => (int)$usedUpToMonth,
                                        'remaining' => (int)($accruedUpToMonth - $usedUpToMonth),
                                    ];
                                }

                                return [
                                    'profile_picture' => $staff->profile_picture ?? null,
                                    'name' => ($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''),
                                    'position' => $staff->position->position_name ?? 'N/A',
                                    'role' => $user->role->name ?? 'STAFF',
                                    'staff_id' => $staff->id ?? '',
                                    'label_id' => (string) $staff->label_id ?? '',
                                    'gender' => strtoupper(substr($staff->genders ?? 'M', 0, 1)),
                                    'shift_data' => $shiftData,
                                    'leave_balance' => $leaveBalances
                                ];
                            })->values()
                    ];
                })->values();

            return $this->responseHelper->success(
                'Roster fetched successfully',
                [
                    'month' => $month,
                    'year' => $year,
                    'departments' => $data
                ],
                200
            );
        } catch (\Throwable $th) {
            return $this->responseHelper->fail(
                'Failed to fetch roster',
                $th->getMessage(),
                500
            );
        }
    }

    /**
     * Synchronize the OFF day leave balance based on the monthly roster.
     */
    /**
     * Synchronize the OFF day leave balance based on cumulative accrual and usage up to a specific month.
     */
    private function syncOffDayBalance($staffId, $month, $year)
    {
        // 1. Get the "OFF" shift ID
        $offShiftId = DB::table('shifts')->where('name', 'OFF')->value('id');
        if (!$offShiftId) return;

        // 2. Get Staff Joining Date to calculate total entitlement
        $staff = DB::table('staff')->where('id', $staffId)->first();
        if (!$staff || !$staff->date_of_joining) return;

        $joiningDate = \Carbon\Carbon::parse($staff->date_of_joining);

        // Use the end of the specified roster month for calculation
        $rosterMonthDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate total months since joining up to the target month
        if ($joiningDate->greaterThan($rosterMonthDate)) {
            $totalAccruedDays = 0;
        } else {
            $totalMonths = $joiningDate->diffInMonths($rosterMonthDate) + 1;
            $totalAccruedDays = $totalMonths * 4;
        }

        // 3. Count roster entries marked as OFF up to the target month
        $totalUsedOffDays = DB::table('rosters')
            ->where('staff_id', $staffId)
            ->where('shift_id', $offShiftId)
            ->where('work_date', '<=', $rosterMonthDate->format('Y-m-d'))
            ->count();

        // 4. Get the "OFF Day" leave type ID
        $offLeaveTypeId = DB::table('leave_types')
            ->where('name', 'LIKE', '%OFF%')
            ->value('id');

        if ($offLeaveTypeId) {
            // 5. Update or Create the leave_balance record
            DB::table('leave_balance')->updateOrInsert(
                [
                    'staff_id' => $staffId,
                    'leave_type_id' => $offLeaveTypeId
                ],
                [
                    'total_days' => $totalAccruedDays,
                    'used_days' => $totalUsedOffDays,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())')
                ]
            );
        }
    }
}
