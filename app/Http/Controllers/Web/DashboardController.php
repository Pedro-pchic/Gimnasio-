<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Service;
use App\PaymentStatus;
use App\SaleStatus;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now();
        $todaySchedules = GymClassSchedule::query()
            ->with('gymClass')
            ->withCount([
                'enrollments as enrolled_today_count' => fn ($query) => $query
                    ->whereDate('enrollment_date', $today)
                    ->capacityBlocking(),
            ])
            ->where('is_active', true)
            ->where('day_of_week', strtolower($today->englishDayOfWeek))
            ->whereHas('gymClass', fn ($query) => $query->where('is_active', true))
            ->get();

        return view('dashboard', [
            'statistics' => [
                ['label' => 'Sucursales activas', 'value' => Branch::query()->where('is_active', true)->count()],
                ['label' => 'Clientes activos', 'value' => Client::query()->where('is_active', true)->count()],
                ['label' => 'Tipos de membresía', 'value' => MembershipType::query()->where('is_active', true)->count()],
                ['label' => 'Membresías activas', 'value' => ClientMembership::query()->where('status', ClientMembershipStatus::Active)->count()],
                ['label' => 'Servicios activos', 'value' => Service::query()->where('is_active', true)->count()],
                ['label' => 'Pagos cobrados hoy', 'value' => Payment::query()->where('status', PaymentStatus::Paid)->whereDate('payment_date', today())->count()],
                ['label' => 'Ventas completadas hoy', 'value' => Sale::query()->where('status', SaleStatus::Completed)->whereDate('sale_date', today())->count()],
                ['label' => 'Renovaciones recientes', 'value' => ClientMembership::query()->whereHas('payments')->where('created_at', '>=', now()->subDays(7))->count()],
                ['label' => 'Clases de hoy', 'value' => $todaySchedules->count()],
                ['label' => 'Participantes inscritos hoy', 'value' => GymClassEnrollment::query()->capacityBlocking()->whereDate('enrollment_date', $today)->count()],
                ['label' => 'Clases llenas', 'value' => $todaySchedules->filter(fn (GymClassSchedule $schedule): bool => $schedule->enrolled_today_count >= $schedule->gymClass->maximum_capacity)->count()],
                ['label' => 'Cupos disponibles hoy', 'value' => $todaySchedules->sum(fn (GymClassSchedule $schedule): int => max(0, $schedule->gymClass->maximum_capacity - $schedule->enrolled_today_count))],
            ],
        ]);
    }
}
