<?php

namespace App\Http\Controllers;

use App\Mail\ClientOnboardNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

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

        // Check if the user already has a client with the same email
        $existingClient = Clients::where('user_id', $request->user()->id)
            ->where('email', $validatedData['email'])
            ->first();

        if ($existingClient) {
            return response()->json(['error' => 'Client with this email already exists'], 409);
        }

        // Create an onboarding token for the client
        $onboardingToken = Str::random(32);

        // Create a new client
        $client = new Clients();

        $client->user_id = $request->user()->id;
        $client->name = $validatedData['name'];
        $client->email = $validatedData['email'];
        $client->phone = $validatedData['phone'];
        $client->address = $validatedData['address'];

        // Set the onboarding token
        $client->onboard_token = $onboardingToken;
        $client->onboard_status = 'pending';
        $client->onboard_token_expires_at = now()->addDays(7);

        // Email the client with the onboarding link
        Mail::to($client->email)->send(new ClientOnboardNotification($request->user(), $client));

        // Save the client to the database
        if ($client->save()) {
            return response()->json(['message' => 'Client created successfully! An email was sent so they can onboard!', 'client' => $client], 201);
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
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

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

        // Update the client
        $client->name = $validatedData['name'];
        $client->email = $validatedData['email'];
        $client->phone = $validatedData['phone'];
        $client->address = $validatedData['address'];

        // Save the client to the database
        if ($client->save()) {
            return response()->json(['message' => 'Client updated successfully', 'client' => $client], 200);
        } else {
            return response()->json(['error' => 'Failed to update client'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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

        // Delete the client
        if ($client->delete()) {
            return response()->json(['message' => 'Client deleted successfully'], 200);
        } else {
            return response()->json(['error' => 'Failed to delete client'], 500);
        }
    }
}
