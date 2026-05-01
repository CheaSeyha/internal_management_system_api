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

        $isSuperAdmin = $user->role_id == 1; // HR / Company Owner
        $isAdmin = $user->role_id == 2;      // Shift Manager / Roster Editor

        // Authorization Check
        if (!$isSuperAdmin) {
            foreach ($data['staff_roster'] as $staffItem) {
                $targetStaffId = $staffItem['staff_id'];

                if ($isAdmin) {
                    // Admin (Shift Manager) can only edit staff in their own department
                    // We need to check the target staff's department
                    $targetStaff = DB::table('staff')->where('staff_id', $targetStaffId)->first();
                    if (!$targetStaff || $targetStaff->department_id != $user->staff->department_id) {
                        return $this->responseHelper->fail('Unauthorized. You can only update rosters for staff in your department.', null, 403);
                    }
                } else {
                    // Regular users can only edit their own roster
                    if ($targetStaffId != $user->staff_id) {
                        return $this->responseHelper->fail('Unauthorized. You can only update your own roster.', null, 403);
                    }
                }
            }
        }

        return DB::transaction(function () use ($data) {
            try {
                $staffRosterResults = [];

                foreach ($data['staff_roster'] as $staffItem) {
                    $staffId = $staffItem['staff_id'];
                    $rosterResults = [];

                    foreach ($staffItem['roster'] as $item) {
                        $workDate = $item['date'];
                        $shiftId = $item['shift_id'];

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
                            'shift_id' => $shiftId,
                            'action' => $roster->wasRecentlyCreated ? 'create' : 'update',
                        ];
                    }

                    $staffRosterResults[] = [
                        'staff_id' => (string) $staffId,
                        'roster' => $rosterResults
                    ];
                }

                $responseData = [
                    'staff_roster' => $staffRosterResults
                ];

                return $this->responseHelper->success('Roster created or updated successfully', $responseData, 200);
            } catch (\Throwable $th) {
                return $this->responseHelper->fail('Failed to create or update roster', $th->getMessage(), 500);
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
            $year = $year ?: date('Y');

            $rosters = $this->rosterRepository->getAllRoster($month, $year, $departmentId);

            $data = $rosters
                // group by department first
                ->groupBy(function ($item) {
                    return $item->staff->department->id ?? 0;
                })
                ->map(function ($departmentRosters) use ($month, $year) {

                    $first = $departmentRosters->first();
                    $department = $first->staff->department ?? null;

                    return [
                        'department_id' => $department->id ?? null,
                        'department_name' => $department->department_name ?? 'Unknown',

                        // group staff inside department
                        'staffs' => $departmentRosters
                            ->groupBy('staff_id')
                            ->map(function ($staffRosters) use ($month, $year) {

                                $firstRoster = $staffRosters->first();
                                $staff = $firstRoster->staff ?? null;
                                $user = $staff->user ?? null;

                                // Determine days in month
                                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
                                $rostersByDay = $staffRosters->keyBy(fn($r) => (int)$r->work_date->format('d'));

                                $shiftData = [];
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $roster = $rostersByDay->get($day);
                                    if (!$roster) {
                                        $shiftData[] = "7";
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
                                });

                                return [
                                    'profile_picture' => $staff->profile_picture ?? null,
                                    'name' => ($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''),
                                    'position' => $staff->position->position_name ?? 'N/A',
                                    'role' => $user->role->name ?? 'STAFF',
                                    'staff_id' => $staff->label_id ?? (string) $staff->staff_id,
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
}
