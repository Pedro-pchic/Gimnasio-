<?php

namespace Tests\Feature;

use App\DiscountType;
use App\Models\Branch;
use App\Models\CommercialPartner;
use App\Models\Discount;
use App\Models\Role;
use App\Models\Sale;
use App\Models\ThirdPartyItem;
use App\Models\User;
use App\ThirdPartyItemType;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ThirdPartyCommerceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_administrator_manages_partners_items_branches_and_discounts(): void
    {
        $administrator = $this->userWithRole('Administrador general');
        $branch = Branch::factory()->create();

        $this->actingAs($administrator)->get('/commercial-partners/create')->assertOk();
        $this->actingAs($administrator)->post('/commercial-partners', [
            'name' => 'Proveedor deportivo',
            'type' => 'mixed',
            'contact' => 'Maria Lopez',
            'phone' => '5555-1111',
            'email' => 'maria@example.com',
            'is_active' => true,
        ])->assertRedirect();

        $partner = CommercialPartner::query()->where('name', 'Proveedor deportivo')->firstOrFail();

        $this->actingAs($administrator)->get('/third-party-items/create')->assertOk();
        $this->actingAs($administrator)->post('/third-party-items', [
            'third_party_id' => $partner->id,
            'name' => 'Batido proteico',
            'type' => 'product',
            'base_price' => 45,
            'branch_ids' => [$branch->id],
            'is_active' => true,
        ])->assertRedirect();

        $item = ThirdPartyItem::query()->where('name', 'Batido proteico')->firstOrFail();
        $this->actingAs($administrator)->get("/third-party-items/{$item->id}/edit")->assertOk();
        $this->assertDatabaseHas('branch_third_party_item', [
            'branch_id' => $branch->id,
            'third_party_item_id' => $item->id,
        ]);

        $this->actingAs($administrator)->post('/discounts', [
            'name' => 'Promo de lanzamiento',
            'type' => 'percentage',
            'value' => 15,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'third_party_item_ids' => [$item->id],
            'branch_ids' => [$branch->id],
            'is_active' => true,
        ])->assertRedirect();

        $discount = Discount::query()->where('name', 'Promo de lanzamiento')->firstOrFail();
        $this->actingAs($administrator)->get("/discounts/{$discount->id}/edit")->assertOk();
        $this->assertDatabaseHas('discount_third_party_item', [
            'discount_id' => $discount->id,
            'third_party_item_id' => $item->id,
        ]);
        $this->assertDatabaseHas('branch_discount', [
            'discount_id' => $discount->id,
            'branch_id' => $branch->id,
        ]);

        $this->actingAs($administrator)->post('/third-party-items', [
            'third_party_id' => $partner->id,
            'name' => 'Precio invalido',
            'type' => 'product',
            'base_price' => -1,
            'branch_ids' => [$branch->id],
        ])->assertInvalid(['base_price']);

        $this->actingAs($administrator)->post('/discounts', [
            'name' => 'Porcentaje invalido',
            'type' => 'percentage',
            'value' => 101,
            'third_party_item_ids' => [$item->id],
        ])->assertInvalid(['value']);

        $this->actingAs($administrator)->post('/discounts', [
            'name' => 'Fechas invalidas',
            'type' => 'fixed_amount',
            'value' => 10,
            'start_date' => '2026-09-30',
            'end_date' => '2026-09-01',
            'third_party_item_ids' => [$item->id],
        ])->assertInvalid(['end_date']);
    }

    public function test_sale_uses_server_price_and_best_valid_discount_for_external_products_and_services(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $partner = CommercialPartner::factory()->create();
        $product = ThirdPartyItem::factory()->for($partner, 'commercialPartner')->create([
            'name' => 'Bebida externa',
            'type' => ThirdPartyItemType::Product,
            'base_price' => 100,
        ]);
        $service = ThirdPartyItem::factory()->for($partner, 'commercialPartner')->create([
            'name' => 'Fisioterapia externa',
            'type' => ThirdPartyItemType::Service,
            'base_price' => 50,
        ]);
        $product->branches()->attach($branch);
        $service->branches()->attach($branch);

        $percentage = Discount::factory()->create([
            'type' => DiscountType::Percentage,
            'value' => 20,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);
        $fixed = Discount::factory()->create([
            'type' => DiscountType::FixedAmount,
            'value' => 10,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
        ]);
        $percentage->thirdPartyItems()->attach($product);
        $fixed->thirdPartyItems()->attach($service);

        $this->actingAs($receptionist)->get('/sales/create')->assertOk()->assertSee('Bebida externa');
        $this->actingAs($receptionist)->post('/sales', [
            'branch_id' => $branch->id,
            'sale_date' => '2026-09-12',
            'discount' => 10,
            'payment_method' => 'cash',
            'details' => [
                ['concept_type' => 'third_party_product', 'concept_reference_id' => $product->id, 'description' => 'Precio manipulado', 'quantity' => 2, 'unit_price' => 1],
                ['concept_type' => 'third_party_service', 'concept_reference_id' => $service->id, 'description' => 'Servicio alterado', 'quantity' => 1, 'unit_price' => 1],
            ],
        ])->assertRedirect();

        $sale = Sale::query()->firstOrFail();
        $productDetail = $sale->details()->where('concept_reference_id', $product->id)->firstOrFail();
        $serviceDetail = $sale->details()->where('concept_reference_id', $service->id)->firstOrFail();

        $this->assertSame('100.00', $productDetail->unit_price);
        $this->assertSame('40.00', $productDetail->discount);
        $this->assertSame('160.00', $productDetail->subtotal);
        $this->assertSame('50.00', $serviceDetail->unit_price);
        $this->assertSame('10.00', $serviceDetail->discount);
        $this->assertSame('40.00', $serviceDetail->subtotal);
        $this->assertSame('200.00', $sale->subtotal);
        $this->assertSame('10.00', $sale->discount);
        $this->assertSame('190.00', $sale->total);
        $this->assertStringNotContainsString('manipulado', $productDetail->description);
        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 190]);
        $this->assertDatabaseHas('receipts', ['sale_id' => $sale->id, 'number' => sprintf('COMP-%06d', $sale->id)]);
        $this->actingAs($receptionist)->get(route('sales.receipt', $sale))->assertOk()->assertSee('40.00');
    }

    public function test_expired_discount_and_unavailable_branch_are_not_applied_to_sale(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $availableBranch = Branch::factory()->create();
        $unavailableBranch = Branch::factory()->create();
        $item = ThirdPartyItem::factory()->create([
            'type' => ThirdPartyItemType::Product,
            'base_price' => 80,
        ]);
        $item->branches()->attach($availableBranch);
        $expiredDiscount = Discount::factory()->create([
            'type' => DiscountType::Percentage,
            'value' => 50,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-31',
        ]);
        $expiredDiscount->thirdPartyItems()->attach($item);

        $this->actingAs($receptionist)->post('/sales', [
            'branch_id' => $availableBranch->id,
            'sale_date' => '2026-09-12',
            'payment_method' => 'cash',
            'details' => [[
                'concept_type' => 'third_party_product',
                'concept_reference_id' => $item->id,
                'quantity' => 1,
            ]],
        ])->assertRedirect();

        $detail = Sale::query()->firstOrFail()->details()->firstOrFail();
        $this->assertSame('0.00', $detail->discount);
        $this->assertSame('80.00', $detail->subtotal);

        $this->actingAs($receptionist)->post('/sales', [
            'branch_id' => $unavailableBranch->id,
            'sale_date' => '2026-09-12',
            'payment_method' => 'cash',
            'details' => [[
                'concept_type' => 'third_party_product',
                'concept_reference_id' => $item->id,
                'quantity' => 1,
            ]],
        ])->assertInvalid(['details.0.concept_reference_id']);
    }

    public function test_commercial_permissions_limit_management_and_consultation(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $receptionist = $this->userWithRole('Recepcionista');
        $instructor = $this->userWithRole('Instructor / Coach');

        $this->actingAs($supervisor)->get('/commercial-partners')->assertOk();
        $this->actingAs($supervisor)->get('/third-party-items')->assertOk();
        $this->actingAs($supervisor)->get('/discounts')->assertOk();
        $this->actingAs($receptionist)->get('/commercial-partners/create')->assertForbidden();
        $this->actingAs($instructor)->get('/commercial-partners')->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}
