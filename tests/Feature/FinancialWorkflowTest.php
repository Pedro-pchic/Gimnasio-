<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\PaymentStatus;
use App\SaleStatus;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FinancialWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_registers_a_valid_payment_and_rejects_an_invalid_one(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $client = Client::factory()->for(Branch::factory())->create();

        $response = $this->actingAs($user)->post('/payments', [
            'client_id' => $client->id,
            'payment_date' => '2026-09-11',
            'amount' => 275.5,
            'payment_method' => 'transfer',
            'reference' => 'TRX-001',
            'status' => 'paid',
        ]);
        $payment = Payment::query()->firstOrFail();

        $response->assertRedirectToRoute('payments.show', $payment);
        $this->assertSame('275.50', $payment->amount);
        $this->assertSame(PaymentStatus::Paid, $payment->status);

        $this->actingAs($user)
            ->post('/payments', [
                'client_id' => $client->id,
                'payment_date' => '2026-09-11',
                'amount' => 0,
                'payment_method' => 'cash',
            ])
            ->assertInvalid(['amount']);
    }

    public function test_sale_calculates_details_discount_payment_and_receipt_on_the_server(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $client = Client::factory()->for($branch)->create();

        $response = $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'sale_date' => '2026-09-11',
            'discount' => 3,
            'payment_method' => 'cash',
            'details' => [
                ['concept_type' => 'service', 'description' => 'Clase personalizada', 'quantity' => 2, 'unit_price' => 10],
                ['concept_type' => 'other', 'description' => 'Bebida deportiva', 'quantity' => 1, 'unit_price' => 5],
            ],
        ]);
        $sale = Sale::query()->firstOrFail();

        $response->assertRedirectToRoute('sales.receipt', $sale);
        $this->assertSame('25.00', $sale->subtotal);
        $this->assertSame('3.00', $sale->discount);
        $this->assertSame('22.00', $sale->total);
        $this->assertSame(SaleStatus::Completed, $sale->status);
        $this->assertDatabaseCount('sale_details', 2);
        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 22]);
        $this->assertDatabaseHas('receipts', ['sale_id' => $sale->id, 'number' => sprintf('COMP-%06d', $sale->id)]);

        $this->actingAs($user)
            ->post('/sales', [
                'branch_id' => $branch->id,
                'sale_date' => '2026-09-11',
                'discount' => 11,
                'payment_method' => 'cash',
                'details' => [
                    ['concept_type' => 'other', 'description' => 'Producto', 'quantity' => 1, 'unit_price' => 10],
                ],
            ])
            ->assertInvalid(['discount']);

        $this->actingAs($user)
            ->post('/sales', [
                'branch_id' => $branch->id,
                'sale_date' => '2026-09-11',
                'payment_method' => 'cash',
                'details' => [
                    ['concept_type' => 'other', 'description' => 'Producto', 'unit_price' => 10],
                ],
            ])
            ->assertInvalid(['details.0.quantity']);
    }

    public function test_renewal_keeps_membership_history_and_links_its_payment(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $client = Client::factory()->for(Branch::factory())->create();
        $membershipType = MembershipType::factory()->create(['reference_price' => 350]);
        ClientMembership::factory()->for($client)->for($membershipType)->create([
            'status' => ClientMembershipStatus::Expired,
        ]);

        $this->actingAs($user)
            ->post('/renewals', [
                'client_id' => $client->id,
                'membership_type_id' => $membershipType->id,
                'start_date' => '2026-09-12',
                'end_date' => '2026-10-12',
                'payment_date' => '2026-09-11',
                'amount' => 350,
                'payment_method' => 'card',
            ])
            ->assertRedirectToRoute('clients.show', $client);

        $renewal = ClientMembership::query()->latest('id')->firstOrFail();
        $this->assertSame(2, ClientMembership::query()->where('client_id', $client->id)->count());
        $this->assertSame(ClientMembershipStatus::Active, $renewal->status);
        $this->assertDatabaseHas('payments', [
            'client_membership_id' => $renewal->id,
            'client_id' => $client->id,
            'amount' => 350,
        ]);
    }

    public function test_cancelling_payments_and_sales_preserves_financial_history(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $client = Client::factory()->for($branch)->create();
        $payment = Payment::factory()->for($client)->for($user)->create();
        $sale = Sale::factory()->for($client)->for($branch)->for($user)->create();
        $salePayment = Payment::factory()->for($client)->for($user)->for($sale)->create();

        $this->actingAs($user)->patch("/payments/{$payment->id}/cancel")->assertRedirectToRoute('payments.show', $payment);
        $this->actingAs($user)->patch("/sales/{$sale->id}/cancel")->assertRedirectToRoute('sales.show', $sale);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::Cancelled->value]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'status' => SaleStatus::Cancelled->value]);
        $this->assertDatabaseHas('payments', ['id' => $salePayment->id, 'status' => PaymentStatus::Cancelled->value]);
    }

    public function test_financial_permissions_allow_consultation_only_to_supervisor_and_none_to_instructor(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $instructor = $this->userWithRole('Instructor / Coach');
        $client = Client::factory()->for(Branch::factory())->create();

        $this->actingAs($supervisor)->get('/payments')->assertOk();
        $this->actingAs($supervisor)->post('/payments', [
            'client_id' => $client->id,
            'payment_date' => '2026-09-11',
            'amount' => 100,
            'payment_method' => 'cash',
        ])->assertForbidden();
        $this->actingAs($instructor)->get('/payments')->assertForbidden();
    }

    public function test_receipt_failure_rolls_back_sale_details_and_payment(): void
    {
        $user = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $client = Client::factory()->for($branch)->create();
        $existingSale = Sale::factory()->for($client)->for($branch)->for($user)->create();
        Receipt::factory()->for($existingSale)->create(['number' => 'COMP-000002']);

        try {
            $this->actingAs($user)->post('/sales', [
                'client_id' => $client->id,
                'branch_id' => $branch->id,
                'sale_date' => '2026-09-11',
                'discount' => 0,
                'payment_method' => 'cash',
                'details' => [
                    ['concept_type' => 'other', 'description' => 'Producto', 'quantity' => 1, 'unit_price' => 10],
                ],
            ]);
        } catch (QueryException) {
        }

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_details', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}
