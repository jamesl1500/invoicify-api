<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Stripe\Stripe;
use Stripe\PaymentIntent;

use App\Models\Clients;
use App\Models\Payments;
use App\Models\Invoices;

class ClientInvoicesController extends Controller
{
    // Get all invoices
    public function index(Request $request)
    {
        // Make sure the user is authenticated
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the authenticated user
        $user = $request->user();

        // Get all invoices for the user
        $invoices = $user->invoices()->with(['client', 'user'])->get();

        return response()->json($invoices, 200);
    }

    // Pay invoice
    public function pay(Request $request, $id)
    {
        // Make sure the user is authenticated
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the authenticated user
        $user = $request->user();

        // Find the invoice by ID
        $invoice = $user->invoices()->find($id);

        // Check if the invoice exists
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Process payment logic here (e.g., update invoice status, record payment, etc.)
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Validate the request data
        $validatedData = $request->validate([
            'payment_method' => 'required|string|max:255',
        ]);

        // Total amount to be paid
        $totalAmount = $invoice->total_amount * 100; // Amount in cents

        // Processing fee
        $processingFee = $totalAmount * 0.02;

        // Check if the user has a Stripe customer ID
        if (!$invoice->user->stripe_customer_id) {
            // Create a new Stripe customer
            $customer = \Stripe\Customer::create([
                'email' => $invoice->user->email,
                'name' => $invoice->user->name,
                'metadata' => [
                    'user_id' => $invoice->user->id,
                    'role' => 'user',
                ],
            ]);

            // Save the Stripe customer ID to the user
            $invoice->user->stripe_customer_id = $customer->id;
            $invoice->user->save();
        }

        // Create a PaymentIntent
        $paymentIntent = PaymentIntent::create([
            'amount' =>  $totalAmount, // Amount in cents
            'currency' => 'usd',
            'customer' => $user->stripe_customer_id,
            'payment_method' => $request->payment_method,
            'confirm' => true,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => $user->id,
            ],
            'application_fee_amount' => $processingFee, // Amount in cents
            'payment_method_types' => ['card'],
            'description' => 'Payment for Invoice #' . $invoice->invoice_name,
            'transfer_data' => [
                'destination' => $invoice->user->stripe_customer_id,
            ],
        ]);

        // Update the invoice status to 'paid'
        $invoice->status = 'paid';
        $invoice->save();

        // Record the payment in the database (if applicable)
        $payment = new Payment();       

        return response()->json(['message' => 'Payment processed successfully'], 200);
    }
}
