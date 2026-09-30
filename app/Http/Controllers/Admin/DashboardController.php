<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'revenue' => OrderItem::sum(DB::raw('price * quantity')),
            'orders' => Order::count(),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)->count(),
            'animals' => Animal::count(),
            'active_animals' => Animal::where('is_active', true)->count(),
            'sellers' => User::where('is_seller', true)->count(),
            'buyers' => User::where('is_seller', false)->where('is_admin', false)->count(),
            'unread_contacts' => ContactMessage::whereNull('read_at')->count(),
        ];

        $recentOrders = Order::with('buyer')->latest()->take(6)->get();
        $latestAnimals = Animal::with(['seller', 'category'])->latest()->take(5)->get();
        $recentContacts = ContactMessage::latest()->take(4)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'latestAnimals', 'recentContacts'));
    }
}
