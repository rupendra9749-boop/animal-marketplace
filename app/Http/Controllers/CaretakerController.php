<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaretakerRequest;
use App\Models\Caretaker;
use App\Models\Category;
use App\Support\Nearby;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Public search of animal caretakers: within 50 km of the visitor, by animal, service and rate. */
class CaretakerController extends Controller
{
    public const RADII = [10, 25, 50];

    public const DEFAULT_RADIUS = Nearby::CARETAKER_RADIUS_KM;

    public function index(Request $request): View
    {
        $location = Nearby::resolve($request);
        $radius = in_array($request->integer('radius'), self::RADII, true) ? $request->integer('radius') : self::DEFAULT_RADIUS;

        $caretakers = collect();

        if ($location) {
            $caretakers = Caretaker::query()
                ->active()
                ->with('categories')
                ->withinBox($location['lat'], $location['lng'], $radius)
                ->when($request->filled('type'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $request->integer('type'))))
                ->when($request->boolean('boarding'), fn ($q) => $q->where('boarding', true))
                ->when($request->boolean('home'), fn ($q) => $q->where('home_visit', true))
                ->when($request->boolean('available'), fn ($q) => $q->where('available', true))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%'.$request->string('search').'%';
                    $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('headline', 'like', $term)->orWhere('services', 'like', $term));
                })
                ->get()
                ->each(fn (Caretaker $caretaker) => $caretaker->distance_km = $caretaker->distanceFrom($location['lat'], $location['lng']))
                ->filter(fn (Caretaker $caretaker) => $caretaker->distance_km !== null && $caretaker->distance_km <= $radius)
                ->sortBy('distance_km')
                ->values();
        }

        return view('caretakers.index', [
            'caretakers' => Nearby::paginate($caretakers, $request),
            'categories' => Category::orderBy('name')->get(),
            'location' => $location,
            'radius' => $radius,
            'radii' => self::RADII,
            'filtering' => $request->filled('search') || $request->filled('type') || $request->boolean('boarding') || $request->boolean('home') || $request->boolean('available'),
            'total' => Caretaker::active()->count(),
        ]);
    }

    public function show(Caretaker $caretaker): View
    {
        abort_unless($caretaker->is_active, 404);

        $caretaker->load('categories');

        $others = Caretaker::active()->with('categories')->where('id', '!=', $caretaker->id)
            ->when($caretaker->latitude !== null, fn ($q) => $q->withinBox($caretaker->latitude, $caretaker->longitude, self::DEFAULT_RADIUS))
            ->get()
            ->each(fn (Caretaker $other) => $other->distance_km = $caretaker->latitude !== null ? $other->distanceFrom($caretaker->latitude, $caretaker->longitude) : null)
            ->filter(fn (Caretaker $other) => $other->distance_km !== null && $other->distance_km <= self::DEFAULT_RADIUS)
            ->sortBy('distance_km')
            ->take(3)
            ->values();

        return view('caretakers.show', ['caretaker' => $caretaker, 'nearby' => $others]);
    }

    /** "Offer your care services" form. The listing is visible as soon as it is saved (an admin can hide it later). */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isCaretaker()) {
            return view('providers.seller-only', ['what' => 'caretaker']);
        }

        if ($request->user()->caretaker) {
            return redirect()->route('caretaker.profile');
        }

        return view('caretakers.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(CaretakerRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isCaretaker(), 403, __('Only sellers can offer care services.'));
        abort_if($user->caretaker, 409, __('You already have a caretaker profile.'));

        $caretaker = Caretaker::create([
            ...$request->caretakerData(),
            'user_id' => $user->id,
            'slug' => $this->uniqueSlug($request->input('name').' '.$request->input('city')),
            'is_active' => true,
        ]);
        $caretaker->categories()->sync($request->input('categories', []));

        return redirect()->route('caretaker.dashboard')->with('status', __('Your profile is live! People within 50 km can now find you in the caretaker search.'));
    }

    private function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'caretaker';
        $slug = $base;

        for ($i = 2; Caretaker::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
