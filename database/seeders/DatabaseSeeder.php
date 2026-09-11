<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ServiceSeeder::class,
            MembershipTypeSeeder::class,
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->make(['name' => 'Test User'])->only(['name', 'password']),
        );

        $user->roles()->sync([
            Role::query()->where('name', 'Administrador general')->valueOrFail('id'),
        ]);
    }
}
