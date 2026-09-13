<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Gerente', 'description' => 'Gestión administrativa de la sucursal.', 'can_teach' => false],
            ['name' => 'Recepcionista', 'description' => 'Atención y operación de recepción.', 'can_teach' => false],
            ['name' => 'Supervisor', 'description' => 'Supervisión operativa.', 'can_teach' => false],
            ['name' => 'Instructor', 'description' => 'Imparte actividades y clases.', 'can_teach' => true],
            ['name' => 'Coach', 'description' => 'Acompañamiento técnico de actividades.', 'can_teach' => true],
            ['name' => 'Personal de limpieza', 'description' => 'Limpieza y apoyo operativo.', 'can_teach' => false],
        ] as $position) {
            Position::query()->updateOrCreate(
                ['name' => $position['name']],
                [...$position, 'is_active' => true],
            );
        }
    }
}
