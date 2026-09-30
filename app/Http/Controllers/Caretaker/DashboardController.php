<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $caretaker = $user->caretaker?->load('categories');

        return view('caretaker.dashboard', ['caretaker' => $caretaker, 'unread' => $user->unreadMessagesCount()]);
    }
}
