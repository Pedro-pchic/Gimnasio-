<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\BenefitController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ClientMembershipController;
use App\Http\Controllers\Web\CommercialPartnerController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DiscountController;
use App\Http\Controllers\Web\EmployeeAttendanceController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeShiftAssignmentController;
use App\Http\Controllers\Web\GymClassController;
use App\Http\Controllers\Web\GymClassEnrollmentController;
use App\Http\Controllers\Web\GymClassScheduleController;
use App\Http\Controllers\Web\MembershipTypeController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\RenewalController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ServiceController;
use App\Http\Controllers\Web\ThirdPartyItemController;
use App\Http\Controllers\Web\WorkShiftController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest')->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');

    Route::resource('branches', BranchController::class)->only(['index', 'show'])->middleware('can:branches.view');
    Route::resource('branches', BranchController::class)->except(['index', 'show'])->middleware('can:branches.manage');
    Route::patch('/branches/{branch}/status', [BranchController::class, 'toggleStatus'])->middleware('can:branches.manage')->name('branches.toggle-status');

    Route::resource('services', ServiceController::class)->only(['index', 'show'])->middleware('can:services.view');
    Route::resource('services', ServiceController::class)->except(['index', 'show'])->middleware('can:services.manage');
    Route::patch('/services/{service}/status', [ServiceController::class, 'toggleStatus'])->middleware('can:services.manage')->name('services.toggle-status');

    Route::resource('clients', ClientController::class)->only(['index', 'show'])->middleware('can:clients.view');
    Route::resource('clients', ClientController::class)->except(['index', 'show'])->middleware('can:clients.manage');
    Route::patch('/clients/{client}/status', [ClientController::class, 'toggleStatus'])->middleware('can:clients.manage')->name('clients.toggle-status');

    Route::resource('membership-types', MembershipTypeController::class)->only(['index', 'show'])->middleware('can:membership-types.view');
    Route::resource('membership-types', MembershipTypeController::class)->except(['index', 'show'])->middleware('can:membership-types.manage');
    Route::patch('/membership-types/{membershipType}/status', [MembershipTypeController::class, 'toggleStatus'])->middleware('can:membership-types.manage')->name('membership-types.toggle-status');

    Route::resource('benefits', BenefitController::class)->only(['index', 'show'])->middleware('can:benefits.view');
    Route::resource('benefits', BenefitController::class)->except(['index', 'show'])->middleware('can:benefits.manage');
    Route::patch('/benefits/{benefit}/status', [BenefitController::class, 'toggleStatus'])->middleware('can:benefits.manage')->name('benefits.toggle-status');

    Route::resource('commercial-partners', CommercialPartnerController::class)->except(['index', 'show'])->middleware('can:commercial-partners.manage');
    Route::resource('commercial-partners', CommercialPartnerController::class)->only(['index', 'show'])->middleware('can:commercial-partners.view');

    Route::resource('third-party-items', ThirdPartyItemController::class)->except(['index', 'show'])->middleware('can:third-party-items.manage');
    Route::resource('third-party-items', ThirdPartyItemController::class)->only(['index', 'show'])->middleware('can:third-party-items.view');

    Route::resource('discounts', DiscountController::class)->except(['index', 'show'])->middleware('can:discounts.manage');
    Route::resource('discounts', DiscountController::class)->only(['index', 'show'])->middleware('can:discounts.view');

    Route::resource('client-memberships', ClientMembershipController::class)->only(['index', 'show'])->middleware('can:client-memberships.view');
    Route::resource('client-memberships', ClientMembershipController::class)->except(['index', 'show'])->middleware('can:client-memberships.manage');

    Route::resource('payments', PaymentController::class)->only(['index', 'show'])->middleware('can:payments.view');
    Route::resource('payments', PaymentController::class)->only(['create', 'store'])->middleware('can:payments.manage');
    Route::patch('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->middleware('can:payments.manage')->name('payments.cancel');

    Route::resource('sales', SaleController::class)->only(['create', 'store'])->middleware('can:sales.manage');
    Route::resource('sales', SaleController::class)->only(['index', 'show'])->middleware('can:sales.view');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->middleware('can:sales.view')->name('sales.receipt');
    Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->middleware('can:sales.manage')->name('sales.cancel');

    Route::resource('renewals', RenewalController::class)->only(['index'])->middleware('can:renewals.view');
    Route::resource('renewals', RenewalController::class)->only(['create', 'store'])->middleware('can:renewals.manage');

    Route::resource('positions', PositionController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:positions.manage');
    Route::patch('/positions/{position}/status', [PositionController::class, 'toggleStatus'])->middleware('can:positions.manage')->name('positions.toggle-status');

    Route::resource('employees', EmployeeController::class)->only(['index', 'show'])->middleware('can:employees.view');
    Route::resource('employees', EmployeeController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:employees.manage');
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'toggleStatus'])->middleware('can:employees.manage')->name('employees.toggle-status');

    Route::resource('work-shifts', WorkShiftController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:work-shifts.manage');
    Route::patch('/work-shifts/{workShift}/status', [WorkShiftController::class, 'toggleStatus'])->middleware('can:work-shifts.manage')->name('work-shifts.toggle-status');

    Route::resource('employee-shift-assignments', EmployeeShiftAssignmentController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:work-shifts.manage');
    Route::patch('/employee-shift-assignments/{employeeShiftAssignment}/status', [EmployeeShiftAssignmentController::class, 'toggleStatus'])->middleware('can:work-shifts.manage')->name('employee-shift-assignments.toggle-status');

    Route::resource('employee-attendances', EmployeeAttendanceController::class)->only(['index', 'show'])->middleware('can:employee-attendances.view');
    Route::resource('employee-attendances', EmployeeAttendanceController::class)->only(['create', 'store'])->middleware('can:employee-attendances.register');
    Route::patch('/employee-attendances/{employeeAttendance}/checkout', [EmployeeAttendanceController::class, 'checkOut'])->middleware('can:employee-attendances.register')->name('employee-attendances.checkout');

    Route::resource('gym-classes', GymClassController::class)->only(['index', 'show'])->middleware('can:classes.view');
    Route::resource('gym-classes', GymClassController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:classes.manage');
    Route::patch('/gym-classes/{gymClass}/status', [GymClassController::class, 'toggleStatus'])->middleware('can:classes.manage')->name('gym-classes.toggle-status');

    Route::resource('class-schedules', GymClassScheduleController::class)->only(['index'])->middleware('can:schedules.view');
    Route::resource('class-schedules', GymClassScheduleController::class)->only(['create', 'store', 'edit', 'update'])->middleware('can:schedules.manage');

    Route::resource('class-enrollments', GymClassEnrollmentController::class)->only(['index', 'show'])->middleware('can:enrollments.view');
    Route::resource('class-enrollments', GymClassEnrollmentController::class)->only(['create', 'store'])->middleware('can:enrollments.register');
    Route::patch('/class-enrollments/{classEnrollment}/cancel', [GymClassEnrollmentController::class, 'cancel'])->middleware('can:enrollments.register')->name('class-enrollments.cancel');
    Route::patch('/class-enrollments/{classEnrollment}/attendance', [GymClassEnrollmentController::class, 'markAttendance'])->middleware('can:enrollments.attendance')->name('class-enrollments.attendance');
});
