<?php

namespace Database\Seeders;

use App\CommercialPartnerType;
use App\Models\CommercialPartner;
use Illuminate\Database\Seeder;

class CommercialPartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'NutriFit Guatemala', 'type' => CommercialPartnerType::Product, 'contact' => 'Ana Perez'],
            ['name' => 'Wellness Pro', 'type' => CommercialPartnerType::Service, 'contact' => 'Carlos Lopez'],
        ] as $partner) {
            CommercialPartner::query()->updateOrCreate(
                ['name' => $partner['name']],
                [
                    'type' => $partner['type'],
                    'contact' => $partner['contact'],
                    'phone' => '5555-0110',
                    'email' => strtolower(str_replace(' ', '.', $partner['name'])).'@example.com',
                    'observations' => 'Demo commercial partner.',
                    'is_active' => true,
                ],
            );
        }
    }
}
