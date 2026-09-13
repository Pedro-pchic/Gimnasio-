<?php

use App\Http\Controllers\Api\V1\BenefitController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ClientMembershipController;
use App\Http\Controllers\Api\V1\MembershipTypeController;
use App\Http\Controllers\Api\V1\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::apiResource('branches', BranchController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('membership-types', MembershipTypeController::class);
    Route::apiResource('benefits', BenefitController::class);
    Route::apiResource('client-memberships', ClientMembershipController::class);
});
