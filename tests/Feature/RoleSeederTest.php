<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeder_creates_the_initial_administrative_roles(): void
    {
        $this->seed(RoleSeeder::class);

        foreach ([
            'Administrador general',
            'Gerente de sucursal',
            'Recepcionista',
            'Supervisor',
            'Instructor / Coach',
        ] as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role]);
        }
    }
}
