<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Handle an authentication request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Delete previous tokens to avoid duplicates
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $id = \Illuminate\Support\Str::uuid();

        $user = \App\Models\User::create([
            'id' => $id,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return response()->json(['user' => $user], 201);
    }

    /**
     * Handle a password reset request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function passwordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Generate a password reset token
        $token = \Illuminate\Support\Str::random(60);

        // Store the token in the database (you may want to use a dedicated table for this)
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent.'])
            : response()->json(['message' => 'Unable to send reset link.'], 400);
    }

    /**
     * Verify Password Reset Token
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyPasswordReset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        // Check if the token is valid
        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token or email'], 400);
        }

        // Verify the token (you may want to implement your own logic here)
        $recordExists = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (!$recordExists) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        if (!Hash::check($request->token, $recordExists->token)) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        return response()->json(['message' => 'Token is valid']);
    }

    /**
     * Update Password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Check if the token is valid
        $recordExists = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (!$recordExists) {
            return response()->json(['error' => 'Invalid request'], 500);
        }

        if (!Hash::check($request->input('token'), $recordExists->token)) {
            return response()->json(['error' => 'Invalid token'], 500);
        }

        // Check if the password is the same as the current one
        if (Hash::check($request->input('password'), $user->password)) {
            return response()->json(['error' => 'New password cannot be the same as the current password'], 500);
        }

        // Update the password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->password = Hash::make($request->input('password'));
                $user->save();
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password updated successfully']);
        }

        return response()->json(['error' => 'Unable to update password'], 500);
    }



    /**
     * Handle a logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
