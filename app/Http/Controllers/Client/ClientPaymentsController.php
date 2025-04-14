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

        // Get stripe payment info using payment id
        

        // Return the payment as a JSON response
        return response()->json(['payment' => $payment], 200);
    }
}
