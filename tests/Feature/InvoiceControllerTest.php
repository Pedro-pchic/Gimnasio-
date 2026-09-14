<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\PaymentMethod;
use App\PaymentStatus;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_can_view_the_invoice_listing_and_internal_receipt(): void
    {
        $administrator = $this->administrator();
        $client = Client::factory()->create(['first_name' => 'Juan', 'last_name' => 'Pérez']);
        $sale = Sale::factory()->for($client)->for($administrator)->create([
            'sale_date' => '2026-09-14',
            'subtotal' => 100,
            'discount' => 10,
            'total' => 90,
        ]);
        SaleDetail::factory()->for($sale)->create([
            'description' => 'Membresía Premium',
            'quantity' => 1,
            'unit_price' => 100,
            'subtotal' => 100,
        ]);
        Receipt::factory()->for($sale)->create([
            'number' => 'COMP-000001',
            'issued_at' => '2026-09-14',
            'payment_method' => PaymentMethod::Cash,
        ]);
        Payment::factory()->for($sale)->for($client)->create([
            'amount' => 90,
            'payment_method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($administrator)
            ->get(route('invoices.index'))
            ->assertSee('COMP-000001')
            ->assertSee('Membresía Premium')
            ->assertSee('Q90.00');

        $this->actingAs($administrator)
            ->get(route('invoices.show', $sale))
            ->assertSee('Comprobante interno - prototipo académico')
            ->assertSee('Juan Pérez')
            ->assertSee('Efectivo');
    }

    private function administrator(): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $administrator = User::factory()->create();
        $administrator->roles()->attach(Role::query()->where('name', 'Administrador general')->valueOrFail('id'));

        return $administrator->fresh();
    }
}
