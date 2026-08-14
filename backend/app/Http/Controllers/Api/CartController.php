<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        return response()->json(
            Cart::with('animal.images')->where('user_id', Auth::guard('api')->id())->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate(['animal_id' => 'required|exists:animals,id']);

        $cart = Cart::firstOrCreate([
            'user_id' => Auth::guard('api')->id(),
            'animal_id' => $request->animal_id,
        ]);

        return response()->json(['message' => 'Added to cart', 'cart' => $cart], 201);
    }

    public function destroy($animalId)
    {
        Cart::where('user_id', Auth::guard('api')->id())
            ->where('animal_id', $animalId)->delete();

        return response()->json(['message' => 'Removed from cart']);
    }
}
