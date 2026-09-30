<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CompareController extends Controller
{
    const MAX_ITEMS = 4;

    public function index(): View
    {
        $ids = session('compare', []);

        $animals = Animal::with(['category', 'seller'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Animal $animal) => array_search($animal->id, $ids))
            ->values();

        return view('compare.index', [
            'animals' => $animals,
            'bestPickId' => $this->bestPick($animals)?->id,
            'lowestPrice' => $animals->min('price'),
        ]);
    }

    public function store(Animal $animal): RedirectResponse
    {
        $ids = session('compare', []);

        if (in_array($animal->id, $ids)) {
            return back()->with('status', __('":name" is already in your comparison.', ['name' => $animal->name]));
        }

        if (count($ids) >= self::MAX_ITEMS) {
            return back()->with('status', __('You can compare up to :max animals. Remove one first.', ['max' => self::MAX_ITEMS]));
        }

        $ids[] = $animal->id;
        session(['compare' => $ids]);

        return back()->with('status', __('":name" added to compare (:count/:max).', ['name' => $animal->name, 'count' => count($ids), 'max' => self::MAX_ITEMS]));
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        session(['compare' => array_values(array_diff(session('compare', []), [$animal->id]))]);

        return back()->with('status', __('":name" removed from compare.', ['name' => $animal->name]));
    }

    public function clear(): RedirectResponse
    {
        session()->forget('compare');

        return back()->with('status', __('Comparison cleared.'));
    }

    public function fromCart(): RedirectResponse
    {
        $ids = array_slice(array_keys(session('cart', [])), 0, self::MAX_ITEMS);

        session(['compare' => $ids]);

        return redirect()->route('compare.index');
    }

    /**
     * Vaccinated animals are preferred; among those, the cheapest wins.
     *
     * @param  Collection<int, Animal>  $animals
     */
    private function bestPick(Collection $animals): ?Animal
    {
        if ($animals->count() < 2) {
            return null;
        }

        $buyable = $animals->filter(fn (Animal $animal) => $animal->isForSale());
        if ($buyable->count() < 2) {
            return null;
        }

        $pool = $buyable->where('is_vaccinated', true)->where('stock', '>', 0);

        return ($pool->isNotEmpty() ? $pool : $buyable)->sortBy('price')->first();
    }
}
