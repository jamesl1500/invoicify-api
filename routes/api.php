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


Route::get('/debug-token', function (Request $request) {
    $authHeader = $request->header('Authorization');

    if (!$authHeader) {
        return response()->json(['error' => 'No Authorization header'], 401);
    }

    // Extract token from "Bearer <token>"
    $tokenString = str_replace('Bearer ', '', $authHeader);

    // Find the token in the database
    $token = PersonalAccessToken::findToken($tokenString);

    //if (!$token) {
        return response()->json(['error' => 'Invalid token', 'sent_token' => $tokenString, 'token' => $token], 401);
    //}

    //return response()->json(['token_owner' => $token->tokenable]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);
});
