<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\GymClass;
use App\Models\GymClassSchedule;
use App\Models\Service;
use Illuminate\Database\Seeder;

class GymClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::query()->where('code', 'CENTRAL')->first();

        if ($branch === null) {
            return;
        }

        $branch->services()->syncWithoutDetaching(
            Service::query()->whereIn('name', ['Natación', 'Boxeo'])->pluck('id')->all(),
        );

        $gymClass = GymClass::query()->updateOrCreate(
            ['branch_id' => $branch->getKey(), 'name' => 'Clase grupal funcional'],
            [
                'description' => 'Actividad general de acondicionamiento.',
                'type' => 'general',
                'maximum_capacity' => 20,
                'requires_premium' => false,
                'is_active' => true,
            ],
        );
        $swimming = GymClass::query()->updateOrCreate(
            ['branch_id' => $branch->getKey(), 'name' => 'Natación principiantes'],
            [
                'description' => 'Actividad de natación para nivel inicial.',
                'type' => 'natacion',
                'maximum_capacity' => 10,
                'requires_premium' => false,
                'is_active' => true,
            ],
        );
        $boxing = GymClass::query()->updateOrCreate(
            ['branch_id' => $branch->getKey(), 'name' => 'Boxeo técnico'],
            [
                'description' => 'Actividad de boxeo con técnica y acondicionamiento.',
                'type' => 'boxeo',
                'maximum_capacity' => 15,
                'requires_premium' => false,
                'is_active' => true,
            ],
        );

        foreach ([
            [$gymClass, 'tuesday', '18:00:00', '19:00:00'],
            [$swimming, 'monday', '08:00:00', '09:00:00'],
            [$boxing, 'wednesday', '18:00:00', '19:00:00'],
        ] as [$class, $dayOfWeek, $startTime, $endTime]) {
            GymClassSchedule::query()->updateOrCreate(
                [
                    'gym_class_id' => $class->getKey(),
                    'day_of_week' => $dayOfWeek,
                    'start_time' => $startTime,
                ],
                [
                    'end_time' => $endTime,
                    'maximum_capacity' => $class->maximum_capacity,
                    'is_active' => true,
                ],
            );
        }
    }
}
