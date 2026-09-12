<?php

namespace Database\Seeders;

use App\DiscountType;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Discount::query()->updateOrCreate(
            ['name' => 'Descuento de bienvenida externo'],
            [
                'type' => DiscountType::Percentage,
                'value' => 10,
                'start_date' => null,
                'end_date' => null,
                'is_active' => true,
            ],
        );
    }
}
