<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\DashboardController;

use App\Models\PersonalAccessToken;

use App\Http\Controllers\client\Auth\ClientAuthController;
use App\Http\Controllers\client\ClientDashboardController;
use App\Http\Controllers\client\ClientInvoicesController;
use App\Http\Controllers\client\ClientPaymentsController;
use App\Http\Controllers\client\ClientProfileController;
use App\Http\Controllers\client\ClientSettingsController;


/**
 * Login Route
 */
Route::post('/login', [AuthController::class, 'authenticate']);

/**
 * Register Route
 */
Route::post('/register', [AuthController::class, 'register']);

/**
 * Logout Route
 */
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

/**
 * User Route
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Validate Token Route
 */
Route::get('/validate-token', function (Request $request) {
    $token = PersonalAccessToken::findToken($request->bearerToken());
    if ($token) {
        return response()->json(['valid' => true]);
    }
    return response()->json(['valid' => false], 401);
})->middleware('auth:sanctum');

/**
 * Client Portal Routes
 */
Route::prefix('client')->group(function () {
    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/onboard', [ClientAuthController::class, 'onboard']);
    Route::post('/logout', [ClientAuthController::class, 'logout'])->middleware('auth:client');

    Route::get('/verifyOnboardToken/{id}', [ClientAuthController::class, 'verifyOnboardToken']);

    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->middleware('auth:client');
    Route::get('/invoices', [ClientInvoicesController::class, 'index'])->middleware('auth:client');
    Route::get('/payments', [ClientPaymentsController::class, 'index'])->middleware('auth:client');
    Route::get('/profile', [ClientProfileController::class, 'index'])->middleware('auth:client');
    Route::get('/settings', [ClientSettingsController::class, 'index'])->middleware('auth:client');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);

    Route::get('/dashboard', [DashboardController::class, 'index']);
});
