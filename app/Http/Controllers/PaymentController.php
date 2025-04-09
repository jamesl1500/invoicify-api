<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payments;
use Stripe\Stripe;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the payments for the authenticated user
        $payments = Payments::where('user_id', $user->id)->with(['user','invoice','client'])->get();

        // Check if the user has any payments
        if ($payments->isEmpty()) {
            return response()->json(['message' => 'No payments found', 'payments' => array()], 404);
        }

        // Return the payments as a JSON response
        return response()->json(['payments' => $payments], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the payment by ID
        $payment = Payments::where('user_id', $user->id)->find($id)->with(['user', 'invoice', 'client'])->first();

        // Check if the payment exists
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Return the payment as a JSON response
        return response()->json(['payment' => $payment], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Refund the specified resource in storage.
     */
    public function refund(Request $request, string $id)
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the payment by ID
        $payment = Payments::find($id)->with(['user', 'client'])->first();

        // Check if the payment exists
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Process refund logic here (e.g., update payment status, record refund, etc.)
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $payment->transaction_id,
                'amount' => $payment->amount * 100, // Convert to cents
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Refund failed: ' . $e->getMessage()], 500);
        }

        // Update payment status to refunded
        $payment->status = 'refunded';
        $payment->save();

        return response()->json(['message' => 'Payment refunded successfully', 'refund' => $refund], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
