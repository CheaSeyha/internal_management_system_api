<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Repository\RosterRepository;

class RosterSerivce
{
    /**
     * Create a new class instance.
     */
    protected RosterRepository $rosterRepository;
    protected ResponseHelper $responseHelper;
    public function __construct(RosterRepository $rosterRepository, ResponseHelper $responseHelper)
    {
        //
        $this->rosterRepository = $rosterRepository;
        $this->responseHelper = $responseHelper;
    }

    public function createOrUpdateRoster(array $data)
    {
        try {
            $results = [];

            foreach ($data['rosters'] as $item) {

                // 1. Check existing roster
                $existing = $this->rosterRepository->findByStaffAndDate(
                    $data['staff_id'],
                    $item['work_date']
                );

                if ($existing) {
                    // 2. UPDATE
                    $this->rosterRepository->update($existing, [
                        'shift_id' => $item['shift_id'],
                    ]);

                    $results[] = [
                        'action' => 'updated',
                        'data' => $existing->fresh(),
                    ];
                } else {
                    // 3. CREATE
                    $created = $this->rosterRepository->create([
                        'staff_id'  => $data['staff_id'],
                        'shift_id'  => $item['shift_id'],
                        'work_date' => $item['work_date'],
                    ]);

                    $results[] = [
                        'action' => 'created',
                        'data' => $created,
                    ];
                }
            }

            return $this->responseHelper->success('Roster created or updated successfully', $results, 200);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('Failed to create or update roster', $th->getMessage(), 500);
        }
    }
}
