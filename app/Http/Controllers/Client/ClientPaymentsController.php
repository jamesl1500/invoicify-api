<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientPaymentsController extends Controller
{
    // Get all payments for the client
    public function index(Request $request)
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the payments for the authenticated client
        $payments = $user->payments()->with(['user', 'invoice', 'client'])->get();

        // Check if the client has any payments
        if ($payments->isEmpty()) {
            return response()->json(['message' => 'No payments found', 'payments' => []], 404);
        }

        // Return the payments as a JSON response
        return response()->json(['payments' => $payments], 200);
    }

    // Get a specific payment for the client
    public function show(Request $request, $id)
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the payment by ID
        $payment = $user->payments()->with(['user', 'invoice', 'client'])->find($id);

        // Check if the payment exists
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Get stripe payment method info
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        try {
            $stripePayment = $stripe->paymentIntents->retrieve($payment->transaction_id, []);
            $paymentMethod = $stripe->paymentMethods->retrieve($stripePayment->payment_method, []);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => 'Stripe API error: ' . $e->getMessage()], 500);
        }

        // Return the payment as a JSON response
        return response()->json(['payment' => $payment, 'payment_info' => $stripePayment, 'payment_method' => $paymentMethod], 200);
    }
}
