<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $invoices = $user->invoices()->with('client')->get();

        return response()->json($invoices, 200);
    }
}
