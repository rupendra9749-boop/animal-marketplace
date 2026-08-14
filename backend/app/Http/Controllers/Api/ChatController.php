<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Conversation between logged-in user and another user (optionally about an animal)
    public function index(Request $request, $userId)
    {
        $myId = Auth::guard('api')->id();

        $chats = Chat::where(function ($q) use ($myId, $userId) {
                $q->where('sender_id', $myId)->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($myId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $myId);
            })
            ->orderBy('created_at')
            ->get();

        return response()->json($chats);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
            'animal_id' => 'nullable|exists:animals,id',
        ]);

        $chat = Chat::create([
            'animal_id' => $request->animal_id,
            'sender_id' => Auth::guard('api')->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Sent', 'chat' => $chat], 201);
    }
}
