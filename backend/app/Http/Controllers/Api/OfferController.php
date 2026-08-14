<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    // Buyer sends offer
    public function store(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'offered_price' => 'required|numeric',
        ]);

        $animal = Animal::findOrFail($request->animal_id);

        $offer = Offer::create([
            'animal_id' => $animal->id,
            'buyer_id' => Auth::guard('api')->id(),
            'seller_id' => $animal->user_id,
            'offered_price' => $request->offered_price,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Offer sent', 'offer' => $offer], 201);
    }

    // Offers received (seller side)
    public function received()
    {
        $offers = Offer::with('animal', 'buyer')
            ->where('seller_id', Auth::guard('api')->id())
            ->latest()->get();

        return response()->json($offers);
    }

    // Offers sent (buyer side)
    public function sent()
    {
        $offers = Offer::with('animal', 'seller')
            ->where('buyer_id', Auth::guard('api')->id())
            ->latest()->get();

        return response()->json($offers);
    }

    // Seller accepts / rejects / counters
    public function respond(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected,countered',
            'counter_price' => 'required_if:status,countered|numeric',
        ]);

        $offer = Offer::where('seller_id', Auth::guard('api')->id())->findOrFail($id);
        $offer->update([
            'status' => $request->status,
            'counter_price' => $request->counter_price ?? null,
        ]);

        return response()->json(['message' => 'Offer updated', 'offer' => $offer]);
    }
}
