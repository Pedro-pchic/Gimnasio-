<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_page_renders_for_guests(): void
    {
        $this->get('/login')
            ->assertSee('Acceso administrativo');
    }

    public function test_administrative_user_can_log_in_with_valid_credentials(): void
    {
        $user = $this->administrativeUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $user = $this->administrativeUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'invalid-password',
        ])->assertInvalid(['email' => 'Las credenciales proporcionadas no son válidas.']);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = $this->administrativeUser();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirectToRoute('login');

        $this->assertGuest();
    }

    public function test_protected_routes_redirect_guests_to_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirectToRoute('login');
    }

    private function administrativeUser(string $roleName = 'Administrador general'): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}
