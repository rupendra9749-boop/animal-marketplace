<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(
            Notification::where('user_id', Auth::guard('api')->id())->latest()->get()
        );
    }

    public function markRead($id)
    {
        $notification = Notification::where('user_id', Auth::guard('api')->id())->findOrFail($id);
        $notification->update(['is_read' => 1]);

        return response()->json(['message' => 'Marked as read']);
    }
}
