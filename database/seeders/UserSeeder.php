<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::factory()->create([
            'staff_id' => null,
            'role_id' => 1,
            'name' => 'Super Admin',
            'email' => 'superAdmin@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('123456789'),
            'account_status' => 'active',
        ]);
    }
}
