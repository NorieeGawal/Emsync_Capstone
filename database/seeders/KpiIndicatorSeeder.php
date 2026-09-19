<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KpiIndicator;

class KpiIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            'Productivity',
            'Work Quality',
            'Efficiency',
            'Timeliness',
            'Attendance',
            'Punctuality',
        ];

        foreach ($indicators as $name) {
            KpiIndicator::firstOrCreate(['name' => $name]);
        }
    }
}