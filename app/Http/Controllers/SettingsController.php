<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Get users basic information like name, email, and phone number
    public function getUserBasicInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        // Assuming the user model has 'name', 'email', and 'phone_number' attributes
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
        ]);
    }

    // Process basic information update
    public function updateUserBasicInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:15',
        ]);

        $user->update($validatedData);

        return response()->json(['message' => 'User information updated successfully']);
    }

    // Change user password
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!password_verify($validatedData['current_password'], $user->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 403);
        }

        $user->update(['password' => bcrypt($validatedData['new_password'])]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    // Get users business information
    public function getCompanyInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Assuming the user model has 'business_name', 'business_address', and 'business_phone' attributes
        return response()->json([
            'company_name' => $user->company_name,
            'company_address' => $user->company_address,
            'company_phone_number' => $user->company_phone_number,
            'company_email' => $user->company_email,
            'company_logo' => $user->company_logo,
        ]);
    }

    // Process business information update
    public function updateCompanyInfo(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:255',
            'company_phone_number' => 'nullable|string|max:15',
            'company_email' => 'nullable|email|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('company_logo')) {
            $logoPath = $request->file('company_logo')->store('logos', 'public');
            $validatedData['company_logo'] = $logoPath;
        }

        $user->update($validatedData);

        return response()->json(['message' => 'Company information updated successfully']);
    }
}
