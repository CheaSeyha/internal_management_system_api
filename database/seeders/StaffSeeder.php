<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = [
            [
                'staff_id' => 1001,
                'label_id' => 'STF-001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'genders' => 'Male',
                'email' => 'john.doe@example.com',
                'phone_number' => '012345678',
                'position_id' => 1,
                'department_id' => 1,
                'status' => 'working',
                'date_of_joining' => '2023-01-01',
            ],
            [
                'staff_id' => 1002,
                'label_id' => 'STF-002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'genders' => 'Female',
                'email' => 'jane.smith@example.com',
                'phone_number' => '087654321',
                'position_id' => 2,
                'department_id' => 1,
                'status' => 'working',
                'date_of_joining' => '2023-02-01',
            ],
            [
                'staff_id' => 1003,
                'label_id' => 'STF-003',
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'genders' => 'Female',
                'email' => 'alice.johnson@example.com',
                'phone_number' => '011223344',
                'position_id' => 3,
                'department_id' => 1,
                'status' => 'working',
                'date_of_joining' => '2023-03-01',
            ],
        ];

        foreach ($staffMembers as $staff) {
            \App\Models\Staff::updateOrCreate(['staff_id' => $staff['staff_id']], $staff);
        }
    }
}
