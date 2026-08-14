<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Commission;
use App\Models\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const COMMISSION_PERCENT = 2;

    // Checkout: create order from accepted offer / direct buy
    public function store(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'final_price' => 'required|numeric',
            'payment_id' => 'required|string',
        ]);

        $animal = Animal::findOrFail($request->animal_id);
        $commissionAmount = round($request->final_price * self::COMMISSION_PERCENT / 100, 2);
        $totalAmount = $request->final_price + $commissionAmount;

        $order = DB::transaction(function () use ($request, $animal, $commissionAmount, $totalAmount) {
            $order = Order::create([
                'animal_id' => $animal->id,
                'buyer_id' => Auth::guard('api')->id(),
                'seller_id' => $animal->user_id,
                'animal_price' => $request->final_price,
                'commission_amount' => $commissionAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'payment_id' => $request->payment_id,
                'order_status' => 'success',
                'delivery_status' => 'not_started',
            ]);

            Commission::create([
                'order_id' => $order->id,
                'amount' => $commissionAmount,
                'status' => 'pending',
                'settled_to_seller' => 0,
            ]);

            $animal->update(['status' => 'sold']);

            return $order;
        });

        return response()->json([
            'message' => 'Order Successful',
            'order' => $order->load('animal', 'seller'),
        ], 201);
    }

    // Buyer: my orders
    public function myOrders()
    {
        $orders = Order::with('animal', 'seller')
            ->where('buyer_id', Auth::guard('api')->id())
            ->latest()->get();

        return response()->json($orders);
    }

    // Seller: received orders
    public function sellerOrders()
    {
        $orders = Order::with('animal', 'buyer')
            ->where('seller_id', Auth::guard('api')->id())
            ->latest()->get();

        return response()->json($orders);
    }
}
