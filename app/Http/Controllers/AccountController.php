<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $stats = [
            'orders' => $user->orders()->count(),
            'spent' => $user->orders()->where('status', '!=', 'cancelled')->sum('total'),
            'wishlist' => $user->wishlist()->count(),
            'cart' => array_sum(session('cart', [])),
            'unread' => $user->unreadMessagesCount(),
        ];

        $recentOrders = $user->orders()->withCount('items')->latest()->take(5)->get();
        $saved = $user->wishlist()->with(['category', 'seller'])->latest('wishlists.created_at')->take(4)->get();

        return view('account.dashboard', compact('stats', 'recentOrders', 'saved'));
    }

    /** A buyer asks to become a seller: they can then sell, list breeding animals and offer doctor / caretaker services. */
    public function becomeSeller(): RedirectResponse
    {
        Auth::user()->forceFill(['is_seller' => true])->save();

        return redirect()->route('seller.dashboard')->with('status', __('You are now a seller. You can sell animals, list breeding animals and create a doctor and a caretaker profile from here.'));
    }
}
