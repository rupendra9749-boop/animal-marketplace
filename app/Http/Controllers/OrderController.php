<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(): View|RedirectResponse
    {
        [$items, $total] = CartController::cartItems();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.create', ['items' => $items, 'total' => $total]);
    }

    public function store(Request $request, OrderNotifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:1000'],
        ]);

        [$items, $total] = CartController::cartItems();

        abort_if($items->isEmpty(), 400, __('Your cart is empty.'));

        $order = DB::transaction(function () use ($items, $total, $validated) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => Order::STATUS_PENDING,
                'total' => $total,
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
            ]);

            foreach ($items as $item) {
                $animal = $item['animal'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'animal_id' => $animal->id,
                    'seller_id' => $animal->user_id,
                    'animal_name' => $animal->name,
                    'quantity' => $item['quantity'],
                    'price' => $animal->price,
                ]);

                $animal->decrement('stock', min($item['quantity'], $animal->stock));
            }

            return $order;
        });

        session()->forget('cart');

        $notifier->placed($order);

        return redirect()->route('orders.show', $order)->with('status', __('Order placed. The seller has been told and will approve it - their contact details are below.'));
    }

    public function index(): View
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->user_id === Auth::id() || Auth::user()->isAdmin(), 403);

        $order->load(['items.seller', 'items.animal']);

        return view('orders.show', compact('order'));
    }
}
