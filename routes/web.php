<?php

use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\BenefitController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\ClientMembershipController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MembershipTypeController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\RenewalController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ServiceController;
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

    Route::resource('client-memberships', ClientMembershipController::class)->only(['index', 'show'])->middleware('can:client-memberships.view');
    Route::resource('client-memberships', ClientMembershipController::class)->except(['index', 'show'])->middleware('can:client-memberships.manage');

    Route::resource('payments', PaymentController::class)->only(['index', 'show'])->middleware('can:payments.view');
    Route::resource('payments', PaymentController::class)->only(['create', 'store'])->middleware('can:payments.manage');
    Route::patch('/payments/{payment}/cancel', [PaymentController::class, 'cancel'])->middleware('can:payments.manage')->name('payments.cancel');

    Route::resource('sales', SaleController::class)->only(['index', 'show'])->middleware('can:sales.view');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->middleware('can:sales.view')->name('sales.receipt');
    Route::resource('sales', SaleController::class)->only(['create', 'store'])->middleware('can:sales.manage');
    Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->middleware('can:sales.manage')->name('sales.cancel');

    Route::resource('renewals', RenewalController::class)->only(['index'])->middleware('can:renewals.view');
    Route::resource('renewals', RenewalController::class)->only(['create', 'store'])->middleware('can:renewals.manage');
});
