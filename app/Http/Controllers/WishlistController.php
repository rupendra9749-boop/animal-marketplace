<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $animals = Auth::user()->wishlist()->with(['seller', 'category'])->latest('wishlists.created_at')->paginate(12);

        return view('wishlist.index', compact('animals'));
    }

    public function store(Animal $animal): RedirectResponse
    {
        Auth::user()->wishlist()->syncWithoutDetaching([$animal->id]);

        return back()->with('status', __('":name" added to your wishlist.', ['name' => $animal->name]));
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        Auth::user()->wishlist()->detach($animal->id);

        return back()->with('status', __('":name" removed from your wishlist.', ['name' => $animal->name]));
    }
}
