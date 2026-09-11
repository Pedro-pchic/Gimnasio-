<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'Gimnasio',
            'Entrenamiento',
            'Clases',
            'Piscina',
            'Natación',
            'Boxeo',
            'Masajes',
        ] as $name) {
            Service::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true],
            );
        }
    }
}
