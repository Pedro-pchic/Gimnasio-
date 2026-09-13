<?php

namespace Database\Seeders;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;

class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Básica', 'reference_price' => 250],
            ['name' => 'Premium', 'reference_price' => 350],
        ] as $membershipType) {
            MembershipType::query()->updateOrCreate(
                ['name' => $membershipType['name']],
                [
                    'reference_price' => $membershipType['reference_price'],
                    'is_active' => true,
                ],
            );
        }
    }
}
