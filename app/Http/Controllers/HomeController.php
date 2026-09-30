<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use App\Support\Nearby;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $location = Nearby::resolve($request);
        $radius = Nearby::ANIMAL_RADIUS_KM;

        $animals = collect();
        $categories = Category::orderBy('name')->get();

        if ($location) {
            // Everything for sale within the radius; category counts come from the same list.
            $nearby = Animal::query()
                ->with(['category', 'seller'])
                ->where('is_active', true)
                ->forSale()
                ->withinBox($location['lat'], $location['lng'], $radius)
                ->latest()
                ->get()
                ->each(fn (Animal $animal) => $animal->distance_km = $animal->distanceFrom($location['lat'], $location['lng']))
                ->filter(fn (Animal $animal) => $animal->distance_km !== null && $animal->distance_km <= $radius);

            $counts = $nearby->countBy('category_id');
            $categories->each(fn (Category $category) => $category->animals_count = $counts->get($category->id, 0));

            $animals = $nearby
                ->when($request->filled('category'), fn ($list) => $list->where('category_id', $request->integer('category')))
                ->when($request->filled('search'), function ($list) use ($request) {
                    $term = mb_strtolower($request->string('search')->toString());

                    return $list->filter(fn (Animal $animal) => str_contains(mb_strtolower($animal->name), $term) || str_contains(mb_strtolower((string) $animal->breed), $term));
                })
                ->sortBy('distance_km')
                ->values();
        }

        $paginated = Nearby::paginate($animals, $request);

        $wishlisted = Auth::check() ? Auth::user()->wishlist()->pluck('animals.id') : collect();
        $compared = collect(session('compare', []));
        $filtering = $request->filled('search') || $request->filled('category');

        $stats = [
            'animals' => $location ? $animals->count() : Animal::where('is_active', true)->forSale()->count(),
            'sellers' => User::where('is_seller', true)->count(),
            'cities' => Animal::where('is_active', true)->whereNotNull('location')->distinct()->count('location'),
        ];

        return view('storefront.index', [
            'animals' => $paginated,
            'categories' => $categories,
            'location' => $location,
            'radius' => $radius,
            'wishlisted' => $wishlisted,
            'compared' => $compared,
            'filtering' => $filtering,
            'stats' => $stats,
        ]);
    }

    public function show(Animal $animal): View
    {
        abort_unless($animal->is_active, 404);

        $related = Animal::query()
            ->with('category')
            ->where('is_active', true)
            ->when($animal->isBreedingOnly(), fn ($q) => $q->forBreeding(), fn ($q) => $q->forSale())
            ->where('category_id', $animal->category_id)
            ->where('id', '!=', $animal->id)
            ->take(4)
            ->get();

        $isWishlisted = Auth::check() && Auth::user()->wishlist()->where('animals.id', $animal->id)->exists();
        $isCompared = in_array($animal->id, session('compare', []));

        return view('storefront.show', compact('animal', 'related', 'isWishlisted', 'isCompared'));
    }
}
