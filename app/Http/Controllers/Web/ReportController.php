<?php

namespace App\Http\Controllers\Web;

use App\ClientMembershipStatus;
use App\EquipmentMaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\EmployeeAttendance;
use App\Models\EquipmentMaintenance;
use App\Models\GymClassEnrollment;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Sale;
use App\PaymentStatus;
use App\SaleStatus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);
        $from = $validated['from'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? today()->toDateString();
        $branchId = $validated['branch_id'] ?? null;

        $sales = Sale::query()->where('status', SaleStatus::Completed)->whereBetween('sale_date', [$from, $to])->when($branchId, fn ($query) => $query->where('branch_id', $branchId));
        $payments = Payment::query()->where('status', PaymentStatus::Paid)->whereBetween('payment_date', [$from, $to])->when($branchId, fn ($query) => $query->whereHas('client', fn ($clientQuery) => $clientQuery->where('branch_id', $branchId)));
        $attendance = EmployeeAttendance::query()->whereBetween('attendance_date', [$from, $to])->when($branchId, fn ($query) => $query->where('branch_id', $branchId));

        return view('reports.index', [
            'branches' => Branch::query()->orderBy('name')->get(),
            'filters' => ['from' => $from, 'to' => $to, 'branch_id' => $branchId],
            'metrics' => [
                ['label' => 'Clientes activos', 'value' => Client::query()->where('is_active', true)->count()],
                ['label' => 'Membresías activas', 'value' => ClientMembership::query()->where('status', ClientMembershipStatus::Active)->count()],
                ['label' => 'Membresías vencidas', 'value' => ClientMembership::query()->whereDate('end_date', '<', today())->count()],
                ['label' => 'Ventas', 'value' => $sales->count()],
                ['label' => 'Ingresos', 'value' => 'Q'.number_format((float) $payments->sum('amount'), 2)],
                ['label' => 'Accesos actuales', 'value' => GymClassEnrollment::query()->whereDate('enrollment_date', today())->count()],
                ['label' => 'Asistencia de empleados', 'value' => $attendance->count()],
                ['label' => 'Inventario bajo', 'value' => InventoryItem::query()->whereNotNull('quantity')->whereNotNull('minimum_stock')->whereColumn('quantity', '<=', 'minimum_stock')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count()],
                ['label' => 'Mantenimientos pendientes', 'value' => EquipmentMaintenance::query()->where('status', EquipmentMaintenanceStatus::Pending)->count()],
            ],
        ]);
    }
}
