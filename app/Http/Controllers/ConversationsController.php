<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversationsController extends Controller
{
    // Show all conversations for user
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get all conversations for the user
        $conversations = $user->conversations()->with(['user', 'client', 'messages'])->get();
        if ($conversations->isEmpty()) {
            return response()->json(['message' => 'No conversations found', 'conversations' => []], 404);
        }

        // Return the conversations as a JSON response
        return response()->json(['conversations' => $conversations], 200);
    }
}
