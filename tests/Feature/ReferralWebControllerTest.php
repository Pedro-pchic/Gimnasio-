<?php

namespace Tests\Feature;

use App\ClientMembershipStatus;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Referral;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\PaymentStatus;
use App\ReferralRewardStatus;
use App\ReferralStatus;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReferralWebControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_receptionist_registers_referral_and_validates_reward_only_after_paid_membership(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $referrer = Client::factory()->create();
        $referred = Client::factory()->create();

        $this->actingAs($receptionist)->post('/referrals', [
            'referrer_client_id' => $referrer->id,
            'referred_client_id' => $referred->id,
        ])->assertRedirect();

        $referral = Referral::query()->firstOrFail();
        $this->assertSame(ReferralStatus::Pending, $referral->status);
        $this->assertSame(ReferralRewardStatus::Pending, $referral->reward_status);
        $this->assertSame('100.00', $referral->reward_amount);

        $this->actingAs($receptionist)
            ->patch("/referrals/{$referral->id}/activate")
            ->assertInvalid(['referral']);

        $membership = ClientMembership::factory()->for($referred)->for(MembershipType::factory())->create([
            'start_date' => today()->subDay(),
            'end_date' => today()->addMonth(),
            'status' => ClientMembershipStatus::Active,
        ]);
        Payment::factory()->for($referred)->for($membership, 'clientMembership')->create([
            'status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($receptionist)
            ->patch("/referrals/{$referral->id}/activate")
            ->assertRedirectToRoute('referrals.show', $referral);

        $referral->refresh();
        $this->assertSame(ReferralStatus::Validated, $referral->status);
        $this->assertSame(ReferralRewardStatus::Available, $referral->reward_status);
        $this->actingAs($receptionist)->get("/clients/{$referrer->id}")->assertOk()->assertSee('Q100.00');
    }

    public function test_referral_rejects_self_referral_duplicate_and_second_referrer(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $firstReferrer = Client::factory()->create();
        $secondReferrer = Client::factory()->create();
        $referred = Client::factory()->create();

        $this->actingAs($receptionist)->post('/referrals', [
            'referrer_client_id' => $referred->id,
            'referred_client_id' => $referred->id,
        ])->assertInvalid(['referred_client_id']);

        $this->actingAs($receptionist)->post('/referrals', [
            'referrer_client_id' => $firstReferrer->id,
            'referred_client_id' => $referred->id,
        ])->assertRedirect();

        $this->actingAs($receptionist)->post('/referrals', [
            'referrer_client_id' => $firstReferrer->id,
            'referred_client_id' => $referred->id,
        ])->assertInvalid(['referred_client_id']);

        $this->actingAs($receptionist)->post('/referrals', [
            'referrer_client_id' => $secondReferrer->id,
            'referred_client_id' => $referred->id,
        ])->assertInvalid(['referred_client_id']);
    }

    public function test_receptionist_applies_partial_credit_and_preserves_its_history(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $referrer = Client::factory()->for($branch)->create();
        $referral = Referral::factory()->for($referrer, 'referrer')->create([
            'status' => ReferralStatus::Validated,
            'reward_amount' => 100,
            'reward_status' => ReferralRewardStatus::Available,
        ]);

        $this->actingAs($receptionist)
            ->post('/sales', $this->salePayload($branch, $referrer, 151))
            ->assertInvalid(['referral_credit_amount']);

        $this->actingAs($receptionist)->post('/sales', $this->salePayload($branch, $referrer, 60))->assertRedirect();

        $firstSale = Sale::query()->firstOrFail();
        $this->assertSame('90.00', $firstSale->total);
        $this->assertDatabaseHas('referral_reward_uses', [
            'referral_id' => $referral->id,
            'sale_id' => $firstSale->id,
            'amount' => 60,
        ]);
        $this->assertSame(60.0, (float) $referral->rewardUses()->sum('amount'));

        $this->actingAs($receptionist)->post('/sales', $this->salePayload($branch, $referrer, 40))->assertRedirect();

        $referral->refresh();
        $this->assertSame(ReferralRewardStatus::Used, $referral->reward_status);
        $this->assertSame(100.0, (float) $referral->rewardUses()->sum('amount'));
        $this->assertDatabaseCount('referral_reward_uses', 2);

        $this->actingAs($receptionist)
            ->post('/sales', $this->salePayload($branch, $referrer, 1))
            ->assertInvalid(['referral_credit_amount']);

        $this->assertDatabaseCount('sales', 2);
    }

    public function test_credit_use_rolls_back_when_receipt_creation_fails(): void
    {
        $receptionist = $this->userWithRole('Recepcionista');
        $branch = Branch::factory()->create();
        $referrer = Client::factory()->for($branch)->create();
        $referral = Referral::factory()->for($referrer, 'referrer')->create([
            'status' => ReferralStatus::Validated,
            'reward_status' => ReferralRewardStatus::Available,
        ]);
        $existingSale = Sale::factory()->for($referrer)->for($branch)->for($receptionist)->create();
        Receipt::factory()->for($existingSale)->create(['number' => 'COMP-000002']);

        try {
            $this->actingAs($receptionist)->post('/sales', $this->salePayload($branch, $referrer, 50));
        } catch (QueryException) {
        }

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('referral_reward_uses', 0);
        $this->assertSame(ReferralRewardStatus::Available, $referral->fresh()->reward_status);
    }

    public function test_referral_permissions_allow_consultation_to_supervisor_and_registration_to_receptionist(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $receptionist = $this->userWithRole('Recepcionista');
        $instructor = $this->userWithRole('Instructor / Coach');

        $this->actingAs($supervisor)->get('/referrals')->assertOk();
        $this->actingAs($supervisor)->get('/referrals/create')->assertForbidden();
        $this->actingAs($receptionist)->get('/referrals/create')->assertOk();
        $this->actingAs($instructor)->get('/referrals')->assertForbidden();
    }

    /**
     * @return array<string, mixed>
     */
    private function salePayload(Branch $branch, Client $client, int $referralCreditAmount): array
    {
        return [
            'client_id' => $client->id,
            'branch_id' => $branch->id,
            'sale_date' => today()->toDateString(),
            'payment_method' => 'cash',
            'referral_credit_amount' => $referralCreditAmount,
            'details' => [[
                'concept_type' => 'other',
                'description' => 'Producto de prueba',
                'quantity' => 1,
                'unit_price' => 150,
            ]],
        ];
    }

    private function userWithRole(string $roleName): User
    {
        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('name', $roleName)->valueOrFail('id'));

        return $user->fresh();
    }
}
