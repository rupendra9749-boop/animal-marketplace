<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $seller = Auth::user();

        $animals = $seller->animals();

        $stats = [
            'animals' => $animals->count(),
            'active_animals' => (clone $animals)->where('is_active', true)->count(),
            'low_stock' => (clone $animals)->where('stock', '<=', 5)->count(),
            // Only animals the seller approved count as sold.
            'revenue' => OrderItem::where('seller_id', $seller->id)->where('status', OrderItem::APPROVED)->sum(DB::raw('price * quantity')),
            'orders' => OrderItem::where('seller_id', $seller->id)->distinct('order_id')->count('order_id'),
            'waiting' => OrderItem::where('seller_id', $seller->id)->where('status', OrderItem::PENDING)->distinct('order_id')->count('order_id'),
            'breeding' => $seller->animals()->forBreeding()->count(),
            'unread' => $seller->unreadMessagesCount(),
        ];

        $recentSales = OrderItem::with(['order.buyer', 'animal'])
            ->where('seller_id', $seller->id)
            ->latest()
            ->take(6)
            ->get();

        $lowStock = $seller->animals()->where('stock', '<=', 5)->orderBy('stock')->take(4)->get();
        $latest = $seller->animals()->with('category')->latest()->take(4)->get();

        $vet = $seller->vet;
        $caretaker = $seller->caretaker;

        return view('seller.dashboard', compact('stats', 'recentSales', 'lowStock', 'latest', 'vet', 'caretaker'));
    }
}
