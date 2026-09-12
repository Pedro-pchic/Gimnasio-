<?php

namespace Database\Seeders;

use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Matutino', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Vespertino', 'start_time' => '14:00:00', 'end_time' => '22:00:00'],
            ['name' => 'Nocturno', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
        ] as $workShift) {
            WorkShift::query()->updateOrCreate(
                ['name' => $workShift['name']],
                [...$workShift, 'is_active' => true],
            );
        }
    }
}
