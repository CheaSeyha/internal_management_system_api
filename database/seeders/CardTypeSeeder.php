<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CardType;

class CardTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cardTypes = [
            'STAFF',
            'CAR CARD',
            'VIP CARD',
            'CONSTRUCTION',
            'TUKTUK',
            'DELIVERY',
            'ISP',
            'ROLLING',
        ];

        foreach ($cardTypes as $type) {
            CardType::updateOrCreate(
                ['name' => $type],
                ['updated_at' => now()]
            );
        }
    }
}
