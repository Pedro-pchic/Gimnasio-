<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'Administrador general',
            'Gerente de sucursal',
            'Recepcionista',
            'Supervisor',
            'Instructor / Coach',
        ] as $name) {
            Role::query()->updateOrCreate(['name' => $name]);
        }
    }
}
