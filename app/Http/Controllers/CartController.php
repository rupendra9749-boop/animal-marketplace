<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        [$items, $total] = $this->cartItems();

        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        abort_unless($animal->is_active, 404);

        if (! $animal->isForSale()) {
            return back()->with('status', __('":name" is offered for breeding only - message the owner to arrange it.', ['name' => $animal->name]));
        }

        $cart = session('cart', []);
        $quantity = $request->integer('quantity', 1);

        $cart[$animal->id] = min(($cart[$animal->id] ?? 0) + $quantity, $animal->stock);
        session(['cart' => $cart]);

        return back()->with('status', __('Added to cart.'));
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session('cart', []);
        $cart[$animal->id] = min($request->integer('quantity'), $animal->stock);
        session(['cart' => $cart]);

        return back()->with('status', __('Cart updated.'));
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$animal->id]);
        session(['cart' => $cart]);

        return back()->with('status', __('Removed from cart.'));
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, array{animal: Animal, quantity: int, subtotal: float}>, 1: float}
     */
    public static function cartItems(): array
    {
        $cart = session('cart', []);
        $animals = Animal::whereIn('id', array_keys($cart))->get()->keyBy('id');

        $items = collect($cart)
            ->map(function ($quantity, $animalId) use ($animals) {
                $animal = $animals->get($animalId);

                if (! $animal || ! $animal->isForSale()) {
                    return null;
                }

                return [
                    'animal' => $animal,
                    'quantity' => $quantity,
                    'subtotal' => round($animal->price * $quantity, 2),
                ];
            })
            ->filter()
            ->values();

        $total = round($items->sum('subtotal'), 2);

        return [$items, $total];
    }
}
