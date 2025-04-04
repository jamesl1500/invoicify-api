<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Get dashboard data
    public function index(Request $request)
    {
        // Make sure the user is authenticated
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the authenticated user
        $user = $request->user();

        // Get total outstanding
        $totalOutstanding = $user->invoices()->where('status', 'pending')->sum('total_amount');

        // Get total paid
        $totalPaid = $user->invoices()->where('status', 'paid')->sum('total_amount');

        // Get recent invoices (5)
        $recentInvoices = $user->invoices()->orderBy('created_at', 'desc')->take(5)->get();

        // Get recent payments (5)
        $recentPayments = $user->payments()->orderBy('created_at', 'desc')->take(5)->get();

        // Get recent clients (5)
        $recentClients = $user->clients()->orderBy('created_at', 'desc')->take(5)->get();

        return response()->json([
            'totalOutstanding' => $totalOutstanding,
            'totalPaid' => $totalPaid,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'recentClients' => $recentClients,
        ], 200);

    }
}
