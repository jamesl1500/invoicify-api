<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Clients;

class ClientController extends Controller
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

        // Get the clients for the authenticated user
        $clients = Clients::where('user_id', $user->id)->get();

        // Check if the user has any clients
        if ($clients->isEmpty()) {
            return response()->json(['message' => 'No clients found'], 404);
        }

        // Return the clients as a JSON response
        return response()->json(['clients' => $clients], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Make sure the user is authenticated
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Create a new client
        $client = new Clients();

        $client->user_id = $request->user()->id;
        $client->name = $validatedData['name'];
        $client->email = $validatedData['email'];
        $client->phone = $validatedData['phone'];
        $client->address = $validatedData['address'];

        // Save the client to the database
        if ($client->save()) {
            return response()->json(['message' => 'Client created successfully', 'client' => $client], 201);
        } else {
            return response()->json(['error' => 'Failed to create client'], 500);
        }
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

        // Find the client by ID
        $client = Clients::where('user_id', $user->id)->where('id', $id)->first();

        // Check if the client exists
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }

        // Check if the client belongs to the authenticated user
        if ($client->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }


        // Get the clients invoices
        $invoices = $client->invoices()->get();

        // Get the clients payments
        $payments = $client->payments()->get();

        // Return the client as a JSON response
        return response()->json(['client' => $client, 'invoices' => $invoices, 'payments' => $payments], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
