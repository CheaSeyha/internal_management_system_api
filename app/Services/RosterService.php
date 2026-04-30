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
                ->map(function ($departmentRosters) {

                    $first = $departmentRosters->first();
                    $department = $first->staff->department ?? null;

                    return [
                        'department_id' => $department->id ?? null,
                        'department_name' => $department->department_name ?? 'Unknown',

                        // group staff inside department
                        'staffs' => $departmentRosters
                            ->groupBy('staff_id')
                            ->map(function ($staffRosters) {

                                $firstRoster = $staffRosters->first();
                                $staff = $firstRoster->staff ?? null;

                                return [
                                    'staff_id' => (string) ($staff->id ?? null),
                                    'first_name' => $staff->first_name ?? null,
                                    'last_name' => $staff->last_name ?? null,

                                    'roster' => $staffRosters->map(function ($roster) {
                                        return [
                                            'date' => $roster->work_date,
                                            'shift' => [
                                                'name' => $roster->shift->name ?? null,
                                                'start_time' => $roster->shift->start_time ?? null,
                                                'end_time' => $roster->shift->end_time ?? null,
                                            ]
                                        ];
                                    })->values()
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
