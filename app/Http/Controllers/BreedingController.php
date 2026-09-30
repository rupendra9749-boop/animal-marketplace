<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Category;
use App\Services\BreedingMatcher;
use App\Support\Nearby;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BreedingController extends Controller
{
    public function __construct(private BreedingMatcher $matcher) {}

    /** Breeding-only marketplace: animals offered for breeding, filterable by type, sex and location. */
    public function browse(Request $request): View|RedirectResponse
    {
        // Old links to the compatibility checker used /breeding?a=..&b=..
        if ($request->has('a') || $request->has('b')) {
            return redirect()->route('breeding.index', $request->only('a', 'b'));
        }

        $sex = in_array($request->string('sex')->toString(), ['male', 'female'], true) ? $request->string('sex')->toString() : null;
        $location = Nearby::resolve($request);
        $radius = Nearby::BREEDING_RADIUS_KM;

        $animals = collect();

        if ($location) {
            $animals = Animal::query()
                ->with(['category', 'seller'])
                ->where('is_active', true)
                ->forBreeding()
                ->withinBox($location['lat'], $location['lng'], $radius)
                ->when($request->filled('type'), fn ($q) => $q->where('category_id', $request->integer('type')))
                ->when($sex, fn ($q) => $q->where('gender', $sex))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%'.$request->string('search').'%';
                    $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('breed', 'like', $term));
                })
                ->get()
                ->each(fn (Animal $animal) => $animal->distance_km = $animal->distanceFrom($location['lat'], $location['lng']))
                ->filter(fn (Animal $animal) => $animal->distance_km !== null && $animal->distance_km <= $radius)
                ->sortBy('distance_km')
                ->values();
        }

        $categories = Category::withCount(['animals' => fn ($q) => $q->where('is_active', true)->forBreeding()])
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $category) => $category->animals_count > 0)
            ->values();

        return view('breeding.browse', [
            'animals' => Nearby::paginate($animals, $request),
            'categories' => $categories,
            'location' => $location,
            'radius' => $radius,
            'sex' => $sex,
            'filtering' => $request->filled('search') || $request->filled('type') || $sex !== null,
            'wishlisted' => Auth::check() ? Auth::user()->wishlist()->pluck('animals.id') : collect(),
            'total' => $location ? $animals->count() : Animal::where('is_active', true)->forBreeding()->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $animals = Animal::with(['category', 'seller'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $byId = $animals->keyBy('id');
        $a = $byId->get($request->integer('a'));
        $b = $byId->get($request->integer('b'));

        $error = null;
        $result = null;
        $suggestions = collect();

        if ($a && $b) {
            if ($a->id === $b->id) {
                $error = __('Please pick two different animals.');
            } else {
                $result = $this->matcher->evaluate($a, $b);
            }
        } elseif ($a) {
            $suggestions = $this->matcher->suggestPartners($a, $animals);
        }

        $compared = collect(session('compare', []))->map(fn ($id) => $byId->get($id))->filter()->values();

        return view('breeding.index', [
            'animals' => $animals->groupBy(fn (Animal $animal) => __($animal->category?->name ?? 'Other')),
            'a' => $a,
            'b' => $b,
            'result' => $result,
            'error' => $error,
            'suggestions' => $suggestions,
            'compared' => $compared,
        ]);
    }

    /** Adds both animals of a pair to the cart (one each, within stock). */
    public function addPairToCart(Request $request): RedirectResponse
    {
        $ids = $request->validate([
            'a' => ['required', 'integer', 'exists:animals,id'],
            'b' => ['required', 'integer', 'different:a', 'exists:animals,id'],
        ]);

        $cart = session('cart', []);
        $added = [];

        foreach (Animal::whereIn('id', [$ids['a'], $ids['b']])->where('is_active', true)->forSale()->get() as $animal) {
            if ($animal->stock < 1) {
                continue;
            }
            $cart[$animal->id] = min(($cart[$animal->id] ?? 0) + 1, $animal->stock);
            $added[] = $animal->name;
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('status', $added
            ? __('Added to your cart: :names.', ['names' => implode(__(' and '), $added)])
            : __('Neither animal is available right now.'));
    }
}
