<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CommercialPartner;
use App\Models\Discount;
use App\Models\ThirdPartyItem;
use App\ThirdPartyItemType;
use Illuminate\Database\Seeder;

class ThirdPartyItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::query()->where('code', 'CENTRAL')->firstOrFail();
        $nutritionPartner = CommercialPartner::query()->where('name', 'NutriFit Guatemala')->firstOrFail();
        $wellnessPartner = CommercialPartner::query()->where('name', 'Wellness Pro')->firstOrFail();

        $proteinBar = ThirdPartyItem::query()->updateOrCreate(
            ['third_party_id' => $nutritionPartner->getKey(), 'name' => 'Barra proteica'],
            [
                'description' => 'Snack proteico de venta externa.',
                'type' => ThirdPartyItemType::Product,
                'base_price' => 25,
                'is_active' => true,
            ],
        );

        $massage = ThirdPartyItem::query()->updateOrCreate(
            ['third_party_id' => $wellnessPartner->getKey(), 'name' => 'Masaje deportivo externo'],
            [
                'description' => 'Servicio de recuperacion ofrecido por un tercero.',
                'type' => ThirdPartyItemType::Service,
                'base_price' => 180,
                'is_active' => true,
            ],
        );

        $proteinBar->branches()->syncWithoutDetaching([$branch->getKey()]);
        $massage->branches()->syncWithoutDetaching([$branch->getKey()]);

        $welcomeDiscount = Discount::query()->where('name', 'Descuento de bienvenida externo')->firstOrFail();
        $welcomeDiscount->thirdPartyItems()->syncWithoutDetaching([$proteinBar->getKey()]);
    }
}
