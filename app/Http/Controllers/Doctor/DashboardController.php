<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $vet = $user->vet?->load('categories');

        return view('doctor.dashboard', ['vet' => $vet, 'unread' => $user->unreadMessagesCount()]);
    }
}
