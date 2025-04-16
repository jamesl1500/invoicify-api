<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientSettingsController extends Controller
{
    //
    // Get all settings for the client

    // Update basic information
    public function updateBasicInformation(Request $request)
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

        // Update the client's basic information
        $user->update($validatedData);

        // Return the updated client data
        return response()->json(['client' => $user], 200);
    }

    // Update password
    public function updatePassword(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the current password is correct
        if (!Hash::check($validatedData['current_password'], $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }

        // Update the user's password
        $user->update(['password' => Hash::make($validatedData['new_password'])]);

        // Return a success message
        return response()->json(['message' => 'Password updated successfully'], 200);
    }
}
