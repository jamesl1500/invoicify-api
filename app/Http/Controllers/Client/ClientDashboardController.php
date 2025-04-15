<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    //
    // Get the dashboard data for the client
    public function index(Request $request)
    {
        // Authenticate
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Get invoices belonging to the client and calculate how much they owe
        $invoices = $user->invoices; 
        $totalOwed = $invoices->sum('total_amount'); 

        // Get payments made by the client
        $payments = $user->payments;
        $totalPaid = $payments->sum('amount');

        return response()->json([
            'invoices' => $invoices,
            'payments' => $payments,
            'total_owed' => $totalOwed,
            'total_paid' => $totalPaid,
        ]);
    }
}
