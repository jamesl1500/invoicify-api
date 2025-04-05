<?php

namespace App\Http\Controllers\client\Auth;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ClientAuthController extends Controller
{
    // Verify onboard token
    public function verifyOnboardToken($id)
    {
        // Validate the token
        if (empty($id) || !is_string($id)) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        // Find the client by token
        $client = Clients::where('onboard_token', $id)->first();

        // Check if the client exists
        if (!$client) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        // Return the client data
        return response()->json([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
            ],
        ], 200);
    }

    // Onboard/register method
    public function onboard(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'onboard_token' => 'required|string|max:255',
            'id' => 'required',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Make sure client exists by checking id, onboard_token, and email
        $client = Clients::where('id', $validatedData['id'])
            ->where('onboard_token', $validatedData['onboard_token'])
            ->where('email', $validatedData['email'])
            ->first();

        // Check if the client exists
        if (!$client) {
            return response()->json(['error' => 'Invalid token or client not found'], 404);
        }

        // Check if the client is already onboarded
        if ($client->password) {
            return response()->json(['error' => 'Client already onboarded'], 409);
        }

        // Hash the password
        $client->password = password_hash($validatedData['password'], PASSWORD_BCRYPT);

        // Update the client data
        $client->name = $validatedData['name'];
        $client->email = $validatedData['email'];
        $client->phone = $validatedData['phone'];
        $client->address = $validatedData['address'];
        $client->onboard_token = null; // Clear the onboard token
        $client->onboard_status = 'onboarded'; // Set the onboard status
        $client->save();

        // Check if the client was saved successfully
        if (!$client->wasChanged()) {
            return response()->json(['error' => 'Failed to onboard client'], 500);
        }

        // Generate a personal access token
        $token = $client->createToken('Client Token')->plainTextToken;

        // Return the token and client data
        return response()->json([
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
            ],
        ], 201);
    }

    // Login method
    public function login(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $client = Clients::where('email', $validatedData['email'])->first();

        // Check if the client exists
        if (!$client) {
            return response()->json(['error' => 'Client not found'], 200);
        }

        // Check if the password is correct
        if (!Hash::check($request->input('password'), $client->password)) {
            return response()->json(['error' => 'Invalid credentials'], 200);
        }

        // Generate a personal access token
        $token = $client->createToken('Client Token')->plainTextToken;

        // Return the token and client data
        return response()->json([
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
            ],
        ], 200);        
    }
}
