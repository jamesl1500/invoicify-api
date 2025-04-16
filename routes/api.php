<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;

use App\Models\PersonalAccessToken;

use App\Http\Controllers\client\Auth\ClientAuthController;
use App\Http\Controllers\client\ClientDashboardController;
use App\Http\Controllers\client\ClientInvoicesController;
use App\Http\Controllers\client\ClientPaymentsController;
use App\Http\Controllers\client\ClientProfileController;
use App\Http\Controllers\client\ClientSettingsController;

use App\Http\Controllers\StripeController;

use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\ConversationsMessagesController;

/**
 * Login Route
 */
Route::post('/login', [AuthController::class, 'authenticate']);

/**
 * Register Route
 */
Route::post('/register', [AuthController::class, 'register']);

/**
 * Password Reset Route
 */
Route::post('/password-reset', [AuthController::class, 'passwordReset']);
Route::post('/password-reset/verify', [AuthController::class, 'verifyPasswordReset']);
Route::post('/password-reset/update', [AuthController::class, 'updatePassword']);

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
 * Client Route
 */
Route::get('/client', function (Request $request) {
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
    Route::get('/payments/{id}', [ClientPaymentsController::class, 'show'])->middleware('auth:client');

    Route::get('/profile', [ClientProfileController::class, 'index'])->middleware('auth:client');

    Route::get('/settings', [ClientSettingsController::class, 'index'])->middleware('auth:client');
    Route::put('/settings/updateBasicInformation', [ClientSettingsController::class, 'updateBasicInformation'])->middleware('auth:client');
    Route::put('/settings/updatePassword', [ClientSettingsController::class, 'updatePassword'])->middleware('auth:client');

    Route::post('invoices/{id}/pay', [ClientInvoicesController::class, 'pay'])->middleware('auth:client');
});

/**
 * Stripe Routes 
*/
Route::prefix('stripe')->group(function () {
    Route::get('/setup-intent', [StripeController::class, 'createSetupIntent'])->middleware('auth:sanctum');
    Route::get('/saved-cards', [StripeController::class, 'getSavedCards'])->middleware('auth:sanctum');

    Route::post('/payment-method', [StripeController::class, 'attachPaymentMethod'])->middleware('auth:sanctum');
    Route::delete('/payment-method/{payment_method_id}', [StripeController::class, 'detachPaymentMethod'])->middleware('auth:sanctum');

    Route::post('/user/onboarding', [StripeController::class, 'userOnboarding'])->middleware('auth:sanctum');
    Route::get('/user/onboarding/verify', [StripeController::class, 'userOnboardingVerify'])->middleware('auth:sanctum');

    Route::post('/client/onboarding', [StripeController::class, 'clientOnboarding'])->middleware('auth:client');
}); 

/**
 *  Conversations / Conversation Messages routes
 */
Route::prefix('conversations')->group(function(){
    Route::get('/', [ConversationsController::class, 'index'])->middleware('auth:sanctum');
});

/**
 * Settings Route
 */
Route::get('/settings/basicInformation', [SettingsController::class, 'getUserBasicInfo'])->middleware('auth:sanctum');
Route::post('/settings/basicInformation', [SettingsController::class, 'updateUserBasicInfo'])->middleware('auth:sanctum');

Route::get('/settings/companyInformation', [SettingsController::class, 'getCompanyInfo'])->middleware('auth:sanctum');
Route::post('/settings/companyInformation', [SettingsController::class, 'updateCompanyInfo'])->middleware('auth:sanctum');

Route::post('/settings/changePassword', [SettingsController::class, 'changePassword'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('clients', ClientController::class);
    
    // Invoices
    Route::apiResource('invoices', InvoiceController::class);

    // Payments
    Route::apiResource('payments', PaymentController::class);
    Route::post('/payments/{id}/refund', [PaymentController::class, 'refund']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
});
