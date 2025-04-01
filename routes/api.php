<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;

use App\Models\PersonalAccessToken;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);
});
