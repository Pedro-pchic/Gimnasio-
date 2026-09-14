<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAccess;
use Illuminate\Database\Seeder;

class ClientAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientId = Client::query()->where('code', 'CLI-000001')->valueOrFail('id');
        $checkedInAt = now()->startOfDay()->setTime(8, 15);

        ClientAccess::query()->firstOrCreate(
            [
                'client_id' => $clientId,
                'checked_in_at' => $checkedInAt,
            ],
            ['checked_out_at' => $checkedInAt->copy()->addHour()],
        );
    }
}
