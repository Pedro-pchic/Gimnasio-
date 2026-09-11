<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\MembershipType;
use App\Models\Service;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'statistics' => [
                ['label' => 'Sucursales activas', 'value' => Branch::query()->where('is_active', true)->count()],
                ['label' => 'Clientes activos', 'value' => Client::query()->where('is_active', true)->count()],
                ['label' => 'Tipos de membresía', 'value' => MembershipType::query()->where('is_active', true)->count()],
                ['label' => 'Membresías activas', 'value' => ClientMembership::query()->where('status', ClientMembershipStatus::Active)->count()],
                ['label' => 'Servicios activos', 'value' => Service::query()->where('is_active', true)->count()],
            ],
        ]);
    }
}
