<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** A seller's incoming orders: who bought, how to reach them, and Approve / Decline. */
class SaleController extends Controller
{
    public function index(): View
    {
        $sellerId = Auth::id();

        // One card per order, showing only this seller's animals in it.
        $orders = Order::query()
            ->whereHas('items', fn ($q) => $q->where('seller_id', $sellerId))
            ->with(['buyer', 'items' => fn ($q) => $q->where('seller_id', $sellerId)->with('animal')])
            ->latest()
            ->paginate(10);

        // Only approved animals count as sold; waiting and declined ones do not.
        $approved = OrderItem::where('seller_id', $sellerId)->where('status', OrderItem::APPROVED);

        $totals = [
            'revenue' => (clone $approved)->sum(DB::raw('price * quantity')),
            'units' => (clone $approved)->sum('quantity'),
            'orders' => OrderItem::where('seller_id', $sellerId)->distinct('order_id')->count('order_id'),
            'waiting' => OrderItem::where('seller_id', $sellerId)->where('status', OrderItem::PENDING)->distinct('order_id')->count('order_id'),
        ];

        return view('seller.sales.index', compact('orders', 'totals'));
    }

    public function approve(Request $request, Order $order, OrderNotifier $notifier): RedirectResponse
    {
        return $this->decide($request, $order, OrderItem::APPROVED, $notifier);
    }

    public function decline(Request $request, Order $order, OrderNotifier $notifier): RedirectResponse
    {
        return $this->decide($request, $order, OrderItem::DECLINED, $notifier);
    }

    /** Decides every waiting animal of this seller in the order; an admin may decide for a seller (?seller_id=). */
    private function decide(Request $request, Order $order, string $decision, OrderNotifier $notifier): RedirectResponse
    {
        $user = Auth::user();
        $sellerId = $user->isAdmin() && $request->filled('seller_id') ? $request->integer('seller_id') : $user->id;

        $items = $order->items()->where('seller_id', $sellerId)->where('status', OrderItem::PENDING)->with(['animal', 'seller'])->get();

        if ($items->isEmpty()) {
            return back()->with('status', __('There is nothing waiting for a decision in this order.'));
        }

        DB::transaction(function () use ($items, $decision) {
            foreach ($items as $item) {
                $item->update(['status' => $decision, 'decided_at' => now()]);

                // A declined animal goes back on sale.
                if ($decision === OrderItem::DECLINED && $item->animal) {
                    $item->animal->increment('stock', $item->quantity);
                }
            }
        });

        $order->syncStatus();
        $notifier->decided($order, $items->first()->seller, $items, $decision);

        return back()->with('status', $decision === OrderItem::APPROVED
            ? __('Order #:id approved. The buyer has been emailed your contact details.', ['id' => $order->id])
            : __('Order #:id declined. The animals are back on sale and the buyer has been told.', ['id' => $order->id]));
    }
}
