<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'IT',
            'HR',
            'Finance',
            'Sales',
            'Marketing',
            'Production',
            'Quality Assurance',
            'Logistics',
        ];

        foreach ($departments as $department) {
            Department::create([
                'department_name' => $department
            ]);
        }
    }
}
