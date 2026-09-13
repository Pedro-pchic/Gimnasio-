<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\EquipmentMaintenanceStatus;
use App\Http\Controllers\Controller;
use App\InventoryItemStatus;
use App\InventoryItemType;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\CommercialPartner;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EquipmentMaintenance;
use App\Models\GymClassEnrollment;
use App\Models\GymClassSchedule;
use App\Models\InventoryItem;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use App\Models\ThirdPartyItem;
use App\PaymentStatus;
use App\ReferralRewardStatus;
use App\SaleDetailType;
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

        $thirdPartySalesToday = SaleDetail::query()
            ->whereIn('concept_type', SaleDetailType::thirdPartyValues())
            ->whereHas('sale', fn ($query) => $query
                ->where('status', SaleStatus::Completed)
                ->whereDate('sale_date', $today))
            ->selectRaw('COUNT(*) as details_count, COALESCE(SUM(subtotal), 0) as total_amount')
            ->firstOrFail();

        return view('dashboard', [
            'statistics' => [
                ['label' => 'Sucursales activas', 'value' => Branch::query()->where('is_active', true)->count()],
                ['label' => 'Clientes activos', 'value' => Client::query()->where('is_active', true)->count()],
                ['label' => 'Tipos de membresía', 'value' => MembershipType::query()->where('is_active', true)->count()],
                ['label' => 'Membresías activas', 'value' => ClientMembership::query()->where('status', ClientMembershipStatus::Active)->count()],
                ['label' => 'Servicios activos', 'value' => Service::query()->where('is_active', true)->count()],
                ['label' => 'Pagos cobrados hoy', 'value' => Payment::query()->where('status', PaymentStatus::Paid)->whereDate('payment_date', today())->count()],
                ['label' => 'Ventas completadas hoy', 'value' => Sale::query()->where('status', SaleStatus::Completed)->whereDate('sale_date', today())->count()],
                ['label' => 'Referidos del mes', 'value' => Referral::query()->where('created_at', '>=', today()->startOfMonth())->count()],
                ['label' => 'Beneficios de referido disponibles', 'value' => Referral::query()->where('reward_status', ReferralRewardStatus::Available)->count()],
                ['label' => 'Beneficios de referido pendientes', 'value' => Referral::query()->where('reward_status', ReferralRewardStatus::Pending)->count()],
                ['label' => 'Beneficios de referido utilizados', 'value' => Referral::query()->where('reward_status', ReferralRewardStatus::Used)->count()],
                ['label' => 'Artículos activos', 'value' => InventoryItem::query()->where('status', InventoryItemStatus::Active)->count()],
                ['label' => 'Equipos en mantenimiento', 'value' => InventoryItem::query()->where('type', InventoryItemType::Equipment)->where('status', InventoryItemStatus::Maintenance)->count()],
                ['label' => 'Artículos con stock bajo', 'value' => InventoryItem::query()->whereNotNull('quantity')->whereNotNull('minimum_stock')->whereColumn('quantity', '<=', 'minimum_stock')->count()],
                ['label' => 'Mantenimientos pendientes', 'value' => EquipmentMaintenance::query()->where('status', EquipmentMaintenanceStatus::Pending)->count()],
                ['label' => 'Lineas externas vendidas hoy', 'value' => $thirdPartySalesToday->details_count],
                ['label' => 'Monto externo vendido hoy', 'value' => number_format((float) $thirdPartySalesToday->total_amount, 2)],
                ['label' => 'Productos y servicios externos activos', 'value' => ThirdPartyItem::query()->where('is_active', true)->count()],
                ['label' => 'Terceros comerciales activos', 'value' => CommercialPartner::query()->where('is_active', true)->count()],
                ['label' => 'Renovaciones recientes', 'value' => ClientMembership::query()->whereHas('payments')->where('created_at', '>=', now()->subDays(7))->count()],
                ['label' => 'Clases de hoy', 'value' => $todaySchedules->count()],
                ['label' => 'Participantes inscritos hoy', 'value' => GymClassEnrollment::query()->capacityBlocking()->whereDate('enrollment_date', $today)->count()],
                ['label' => 'Clases llenas', 'value' => $todaySchedules->filter(fn (GymClassSchedule $schedule): bool => $schedule->enrolled_today_count >= $schedule->gymClass->maximum_capacity)->count()],
                ['label' => 'Cupos disponibles hoy', 'value' => $todaySchedules->sum(fn (GymClassSchedule $schedule): int => max(0, $schedule->gymClass->maximum_capacity - $schedule->enrolled_today_count))],
                ['label' => 'Empleados activos', 'value' => Employee::query()->active()->count()],
                ['label' => 'Empleados presentes ahora', 'value' => EmployeeAttendance::query()->open()->count()],
                ['label' => 'Entradas laborales de hoy', 'value' => EmployeeAttendance::query()->whereDate('attendance_date', $today)->count()],
                ['label' => 'Asistencias pendientes de salida', 'value' => EmployeeAttendance::query()->open()->count()],
            ],
        ]);
    }
}
