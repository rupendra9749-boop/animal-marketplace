<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        return response()->json(
            Wishlist::with('animal.images')->where('user_id', Auth::guard('api')->id())->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate(['animal_id' => 'required|exists:animals,id']);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => Auth::guard('api')->id(),
            'animal_id' => $request->animal_id,
        ]);

        return response()->json(['message' => 'Added to wishlist', 'wishlist' => $wishlist], 201);
    }

    public function destroy($animalId)
    {
        Wishlist::where('user_id', Auth::guard('api')->id())
            ->where('animal_id', $animalId)->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
