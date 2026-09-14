<?php

namespace Database\Seeders;

use App\ClientMembershipStatus;
use App\Models\Benefit;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use App\Models\MembershipType;
use App\Models\Role;
use App\Models\Service;
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
            PermissionSeeder::class,
            ServiceSeeder::class,
            MembershipTypeSeeder::class,
            PositionSeeder::class,
            WorkShiftSeeder::class,
            CommercialPartnerSeeder::class,
            DiscountSeeder::class,
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            User::factory()->make(['name' => 'Administrador'])->only(['name', 'password']),
        );

        $user->roles()->sync([
            Role::query()->where('name', 'Administrador general')->valueOrFail('id'),
        ]);

        $branch = Branch::query()->updateOrCreate(
            ['code' => 'CENTRAL'],
            [
                'name' => 'Sucursal Central',
                'address' => 'Zona 1',
                'phone' => '5555-0101',
                'is_active' => true,
                'opening_time' => '06:00:00',
                'closing_time' => '21:00:00',
            ],
        );

        $branch->services()->syncWithoutDetaching(
            Service::query()
                ->whereIn('name', ['Gimnasio', 'Entrenamiento'])
                ->pluck('id')
                ->all(),
        );

        $this->call(ThirdPartyItemSeeder::class);

        $membershipType = MembershipType::query()
            ->where('name', 'Premium')
            ->firstOrFail();

        $benefit = Benefit::query()->updateOrCreate(
            ['name' => 'Sesiones de masaje'],
            [
                'description' => 'Dos sesiones de masaje por mes.',
                'benefit_type' => 'usage_limit',
                'value' => 2,
                'usage_limit' => 2,
                'usage_period' => 'month',
                'is_active' => true,
            ],
        );

        $benefit->membershipTypes()->syncWithoutDetaching([$membershipType->getKey()]);

        $client = Client::query()->updateOrCreate(
            ['code' => 'CLI-000001'],
            [
                'branch_id' => $branch->getKey(),
                'first_name' => 'Cliente',
                'last_name' => 'Demo',
                'birth_date' => '1990-01-01',
                'phone' => '5555-0102',
                'email' => 'cliente.demo@example.com',
                'address' => 'Zona 1',
                'registration_date' => '2026-09-01',
                'is_active' => true,
            ],
        );

        ClientMembership::query()->firstOrCreate(
            [
                'client_id' => $client->getKey(),
                'membership_type_id' => $membershipType->getKey(),
            ],
            [
                'start_date' => '2026-09-01',
                'end_date' => '2026-10-01',
                'applied_price' => $membershipType->reference_price,
                'status' => ClientMembershipStatus::Active,
                'observations' => 'Membresía inicial de demostración.',
            ],
        );

        $this->call(ClientAccessSeeder::class);

        $this->call(GymClassSeeder::class);
        $this->call(EmployeeSeeder::class);

        $swimmingSchedule = GymClassSchedule::query()
            ->whereHas('gymClass', fn ($query) => $query->where('name', 'Natación principiantes'))
            ->firstOrFail();

        $swimmingEnrollment = GymClassEnrollment::query()
            ->where('client_id', $client->getKey())
            ->where('gym_class_schedule_id', $swimmingSchedule->getKey())
            ->whereDate('enrollment_date', '2026-09-14')
            ->first();

        if ($swimmingEnrollment === null) {
            GymClassEnrollment::query()->create([
                'client_id' => $client->getKey(),
                'gym_class_schedule_id' => $swimmingSchedule->getKey(),
                'enrollment_date' => '2026-09-14',
                'status' => 'enrolled',
            ]);
        }
    }
}
