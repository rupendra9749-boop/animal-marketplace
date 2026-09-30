<?php

namespace App\Http\Controllers\Breeder;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $animals = $user->animals()->forBreeding();

        $stats = [
            'animals' => (clone $animals)->count(),
            'active' => (clone $animals)->where('is_active', true)->count(),
            'studs' => (clone $animals)->where('gender', 'male')->count(),
            'dams' => (clone $animals)->where('gender', 'female')->count(),
            'unread' => $user->unreadMessagesCount(),
        ];

        $latest = $user->animals()->forBreeding()->with('category')->latest()->take(4)->get();

        return view('breeder.dashboard', compact('stats', 'latest'));
    }
}
