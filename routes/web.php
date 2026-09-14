<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\BenefitController;
use App\Http\Controllers\Web\BiometricAccessController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ClientMembershipController;
use App\Http\Controllers\Web\CommercialPartnerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DiscountController;
use App\Http\Controllers\Web\EmployeeAttendanceController;
use App\Http\Controllers\Web\EmployeeBonusController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeShiftAssignmentController;
use App\Http\Controllers\Web\EquipmentMaintenanceController;
use App\Http\Controllers\Web\GymClassController;
use App\Http\Controllers\Web\GymClassEnrollmentController;
use App\Http\Controllers\Web\GymClassScheduleController;
use App\Http\Controllers\Web\InventoryItemController;
use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\Web\MembershipTypeController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\QualityCertificateController;
use App\Http\Controllers\Web\ReferralController;
use App\Http\Controllers\Web\RenewalController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\ThirdPartyItemController;
use App\Http\Controllers\Web\WorkShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');

    Route::resource('branches', BranchController::class)->except(['index', 'show'])->middleware('can:branches.manage');
    Route::resource('branches', BranchController::class)->only(['index', 'show'])->middleware('can:branches.view');
    Route::patch('/branches/{branch}/status', [BranchController::class, 'toggleStatus'])->middleware('can:branches.manage')->name('branches.toggle-status');

    Route::resource('services', ServiceController::class)->except(['index', 'show'])->middleware('can:services.manage');
    Route::resource('services', ServiceController::class)->only(['index', 'show'])->middleware('can:services.view');
    Route::patch('/services/{service}/status', [ServiceController::class, 'toggleStatus'])->middleware('can:services.manage')->name('services.toggle-status');

    Route::resource('clients', ClientController::class)->except(['index', 'show'])->middleware('can:clients.manage');
    Route::resource('clients', ClientController::class)->only(['index', 'show'])->middleware('can:clients.view');
    Route::patch('/clients/{client}/status', [ClientController::class, 'toggleStatus'])->middleware('can:clients.manage')->name('clients.toggle-status');

    Route::resource('referrals', ReferralController::class)->only(['create', 'store'])->middleware('can:referrals.register');
    Route::resource('referrals', ReferralController::class)->only(['index', 'show'])->middleware('can:referrals.view');
    Route::patch('/referrals/{referral}/activate', [ReferralController::class, 'activate'])->middleware('can:referrals.register')->name('referrals.activate');
    Route::patch('/referrals/{referral}/cancel', [ReferralController::class, 'cancel'])->middleware('can:referrals.manage')->name('referrals.cancel');

    Route::resource('inventory-items', InventoryItemController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:manage_inventory');
    Route::resource('inventory-items', InventoryItemController::class)->only(['index', 'show'])->middleware('can:view_inventory');
    Route::get('/inventory-items/{inventoryItem}/movements', [InventoryItemController::class, 'movements'])->middleware('can:view_inventory')->name('inventory-items.movements');
    Route::post('/inventory-items/{inventoryItem}/movements', [InventoryItemController::class, 'storeMovement'])->middleware('can:register_inventory_movements')->name('inventory-items.movements.store');

    Route::resource('equipment-maintenances', EquipmentMaintenanceController::class)->only(['index'])->middleware('can:view_maintenance');
    Route::resource('equipment-maintenances', EquipmentMaintenanceController::class)->only(['create', 'store'])->middleware('can:manage_maintenance');
    Route::patch('/equipment-maintenances/{equipmentMaintenance}/complete', [EquipmentMaintenanceController::class, 'complete'])->middleware('can:manage_maintenance')->name('equipment-maintenances.complete');
    Route::patch('/equipment-maintenances/{equipmentMaintenance}/cancel', [EquipmentMaintenanceController::class, 'cancel'])->middleware('can:manage_maintenance')->name('equipment-maintenances.cancel');

    Route::resource('suppliers', SupplierController::class)->except(['index', 'show'])->middleware('can:manage_suppliers');
    Route::resource('suppliers', SupplierController::class)->only(['index'])->middleware('can:view_suppliers');
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['create', 'store'])->middleware('can:manage_purchases');
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'show'])->middleware('can:view_purchases');
    Route::patch('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->middleware('can:manage_purchases')->name('purchase-orders.receive');
    Route::resource('quality-certificates', QualityCertificateController::class)->only(['create', 'store'])->middleware('can:manage_purchases');
    Route::resource('quality-certificates', QualityCertificateController::class)->only(['index'])->middleware('can:view_purchases');
    Route::resource('employee-bonuses', EmployeeBonusController::class)->only(['create', 'store'])->middleware('can:manage_bonuses');
    Route::resource('employee-bonuses', EmployeeBonusController::class)->only(['index'])->middleware('can:view_bonuses');
    Route::patch('/employee-bonuses/{employeeBonus}/approve', [EmployeeBonusController::class, 'approve'])->middleware('can:manage_bonuses')->name('employee-bonuses.approve');
    Route::patch('/employee-bonuses/{employeeBonus}/cancel', [EmployeeBonusController::class, 'cancel'])->middleware('can:manage_bonuses')->name('employee-bonuses.cancel');
    Route::resource('reports', ReportController::class)->only(['index'])->middleware('can:view_reports');

    Route::resource('membership-types', MembershipTypeController::class)->except(['index', 'show'])->middleware('can:membership-types.manage');
    Route::resource('membership-types', MembershipTypeController::class)->only(['index', 'show'])->middleware('can:membership-types.view');
    Route::patch('/membership-types/{membershipType}/status', [MembershipTypeController::class, 'toggleStatus'])->middleware('can:membership-types.manage')->name('membership-types.toggle-status');

    Route::resource('benefits', BenefitController::class)->except(['index', 'show'])->middleware('can:benefits.manage');
    Route::resource('benefits', BenefitController::class)->only(['index', 'show'])->middleware('can:benefits.view');
    Route::patch('/benefits/{benefit}/status', [BenefitController::class, 'toggleStatus'])->middleware('can:benefits.manage')->name('benefits.toggle-status');

    Route::resource('commercial-partners', CommercialPartnerController::class)->except(['index', 'show'])->middleware('can:commercial-partners.manage');
    Route::resource('commercial-partners', CommercialPartnerController::class)->only(['index', 'show'])->middleware('can:commercial-partners.view');

    Route::resource('third-party-items', ThirdPartyItemController::class)->except(['index', 'show'])->middleware('can:third-party-items.manage');
    Route::resource('third-party-items', ThirdPartyItemController::class)->only(['index', 'show'])->middleware('can:third-party-items.view');

    Route::resource('discounts', DiscountController::class)->except(['index', 'show'])->middleware('can:discounts.manage');
    Route::resource('discounts', DiscountController::class)->only(['index', 'show'])->middleware('can:discounts.view');

    Route::resource('client-memberships', ClientMembershipController::class)->except(['index', 'show'])->middleware('can:client-memberships.manage');
    Route::resource('client-memberships', ClientMembershipController::class)->only(['index', 'show'])->middleware('can:client-memberships.view');

    Route::resource('payments', PaymentController::class)->only(['create', 'store'])->middleware('can:payments.manage');
    Route::resource('payments', PaymentController::class)->only(['index', 'show'])->middleware('can:payments.view');
    Route::patch('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->middleware('can:payments.manage')->name('payments.cancel');

    Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('can:invoices.view')->name('invoices.index');
    Route::get('/invoices/{sale}', [InvoiceController::class, 'show'])->middleware('can:invoices.view')->name('invoices.show');

    Route::resource('sales', SaleController::class)->only(['create', 'store'])->middleware('can:sales.manage');
    Route::resource('sales', SaleController::class)->only(['index', 'show'])->middleware('can:sales.view');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->middleware('can:sales.view')->name('sales.receipt');
    Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->middleware('can:sales.manage')->name('sales.cancel');

    Route::resource('renewals', RenewalController::class)->only(['index'])->middleware('can:renewals.view');
    Route::resource('renewals', RenewalController::class)->only(['create', 'store'])->middleware('can:renewals.manage');

    Route::resource('positions', PositionController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:positions.manage');
    Route::patch('/positions/{position}/status', [PositionController::class, 'toggleStatus'])->middleware('can:positions.manage')->name('positions.toggle-status');

    Route::resource('employees', EmployeeController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:employees.manage');
    Route::resource('employees', EmployeeController::class)->only(['index', 'show'])->middleware('can:employees.view');
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'toggleStatus'])->middleware('can:employees.manage')->name('employees.toggle-status');

    Route::resource('work-shifts', WorkShiftController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:work-shifts.manage');
    Route::patch('/work-shifts/{workShift}/status', [WorkShiftController::class, 'toggleStatus'])->middleware('can:work-shifts.manage')->name('work-shifts.toggle-status');

    Route::resource('employee-shift-assignments', EmployeeShiftAssignmentController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:work-shifts.manage');
    Route::patch('/employee-shift-assignments/{employeeShiftAssignment}/status', [EmployeeShiftAssignmentController::class, 'toggleStatus'])->middleware('can:work-shifts.manage')->name('employee-shift-assignments.toggle-status');

    Route::resource('employee-attendances', EmployeeAttendanceController::class)->only(['create', 'store'])->middleware('can:employee-attendances.register');
    Route::resource('employee-attendances', EmployeeAttendanceController::class)->only(['index', 'show'])->middleware('can:employee-attendances.view');
    Route::patch('/employee-attendances/{employeeAttendance}/checkout', [EmployeeAttendanceController::class, 'checkOut'])->middleware('can:employee-attendances.register')->name('employee-attendances.checkout');

    Route::get('/control-biometrico', [BiometricAccessController::class, 'index'])->middleware('can:biometric-access.view')->name('biometric-access.index');
    Route::post('/control-biometrico', [BiometricAccessController::class, 'store'])->middleware('can:biometric-access.manage')->name('biometric-access.store');

    Route::resource('gym-classes', GymClassController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:classes.manage');
    Route::resource('gym-classes', GymClassController::class)->only(['index', 'show'])->middleware('can:classes.view');
    Route::patch('/gym-classes/{gymClass}/status', [GymClassController::class, 'toggleStatus'])->middleware('can:classes.manage')->name('gym-classes.toggle-status');

    Route::resource('class-schedules', GymClassScheduleController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:schedules.manage');
    Route::resource('class-schedules', GymClassScheduleController::class)->only(['index'])->middleware('can:schedules.view');

    Route::resource('class-enrollments', GymClassEnrollmentController::class)->only(['create', 'store'])->middleware('can:enrollments.register');
    Route::resource('class-enrollments', GymClassEnrollmentController::class)->only(['index', 'show'])->middleware('can:enrollments.view');
    Route::get('/class-reservations', [GymClassEnrollmentController::class, 'reservations'])->middleware('can:enrollments.view')->name('class-reservations.index');
    Route::patch('/class-enrollments/{classEnrollment}/cancel', [GymClassEnrollmentController::class, 'cancel'])->middleware('can:enrollments.register')->name('class-enrollments.cancel');
    Route::patch('/class-enrollments/{classEnrollment}/attendance', [GymClassEnrollmentController::class, 'markAttendance'])->middleware('can:enrollments.attendance')->name('class-enrollments.attendance');
});
