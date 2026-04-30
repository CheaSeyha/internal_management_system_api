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

        // Define admin roles based on existing middleware logic
        $adminRoles = [1, 2]; // 1: super admin, 2: admin
        $isAdmin = in_array($user->role_id, $adminRoles);

        // Authorization Check
        if (!$isAdmin) {
            foreach ($data['staff_roster'] as $staffItem) {
                // Regular users can only edit their own roster
                if ($staffItem['staff_id'] != $user->staff_id) {
                    return $this->responseHelper->fail('Unauthorized. You can only update your own roster.', null, 403);
                }
            }
        }

        return DB::transaction(function () use ($data) {
            try {
                $results = [];

                foreach ($data['staff_roster'] as $staffItem) {
                    $staffId = $staffItem['staff_id'];

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

                        $results[] = [
                            'staff_id' => $staffId,
                            'date' => $workDate,
                            'action' => $roster->wasRecentlyCreated ? 'created' : 'updated',
                        ];
                    }
                }

                return $this->responseHelper->success('Roster created or updated successfully', $results, 200);
            } catch (\Throwable $th) {
                return $this->responseHelper->fail('Failed to create or update roster', $th->getMessage(), 500);
            }
        });
    }
}
