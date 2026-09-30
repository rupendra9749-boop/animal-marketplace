<?php

namespace App\Http\Controllers;

use App\Http\Requests\VetRequest;
use App\Models\Category;
use App\Models\Vet;
use App\Services\AiVetFinder;
use App\Support\Nearby;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VetController extends Controller
{
    /** Search radius choices (km) offered to the buyer - doctors are a local service, never more than 50 km. */
    public const RADII = [10, 25, 50];

    public const DEFAULT_RADIUS = Nearby::VET_RADIUS_KM;

    public function index(Request $request): View
    {
        $location = Nearby::resolve($request);
        $radius = in_array($request->integer('radius'), self::RADII, true) ? $request->integer('radius') : self::DEFAULT_RADIUS;

        $vets = collect();

        if ($location) {
            $vets = Vet::query()
                ->active()
                ->with('categories')
                ->withinBox($location['lat'], $location['lng'], $radius)
                ->when($request->filled('type'), fn ($q) => $q->whereHas('categories', fn ($c) => $c->where('categories.id', $request->integer('type'))))
                ->when($request->boolean('home'), fn ($q) => $q->where('home_visit', true))
                ->when($request->boolean('emergency'), fn ($q) => $q->where('emergency', true))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $term = '%'.$request->string('search').'%';
                    $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('clinic_name', 'like', $term)->orWhere('services', 'like', $term));
                })
                ->get()
                ->each(fn (Vet $vet) => $vet->distance_km = $vet->distanceFrom($location['lat'], $location['lng']))
                ->filter(fn (Vet $vet) => $vet->distance_km !== null && $vet->distance_km <= $radius)
                ->sortBy('distance_km')
                ->values();
        }

        // "Doctors by city": every place that has at least one active doctor, with how many.
        $cityCounts = Vet::active()->selectRaw('state, city, count(*) as total')->groupBy('state', 'city')->orderByDesc('total')->orderBy('city')->get();

        return view('vets.index', [
            'vets' => Nearby::paginate($vets, $request),
            'categories' => Category::orderBy('name')->get(),
            'cityCounts' => $cityCounts,
            'location' => $location,
            'radius' => $radius,
            'radii' => self::RADII,
            'filtering' => $request->filled('search') || $request->filled('type') || $request->boolean('home') || $request->boolean('emergency'),
            'totalVets' => $cityCounts->sum('total'),
            'aiEnabled' => app(AiVetFinder::class)->isConfigured(),
        ]);
    }

    /** Buyer taps "Search with AI": look for real clinics on the web and save what is found. */
    public function aiSearch(Request $request, AiVetFinder $finder): RedirectResponse
    {
        $location = Nearby::resolve($request);

        if (! $location) {
            return redirect()->route('vets.index')->with('status', __('Please choose your city first.'));
        }

        $type = $request->filled('type') ? Category::find($request->integer('type')) : null;

        set_time_limit(100);
        $result = $finder->search($request->user(), $location['state'], $location['city'], $type);

        return redirect()
            ->route('vets.index', array_filter(['state' => $location['state'], 'city' => $location['city'], 'type' => $type?->id]))
            ->with('status', $result->message);
    }

    public function show(Vet $vet): View
    {
        abort_unless($vet->is_active, 404);

        $vet->load('categories');

        $others = Vet::active()->with('categories')->where('id', '!=', $vet->id)
            ->when($vet->latitude !== null, fn ($q) => $q->withinBox($vet->latitude, $vet->longitude, self::DEFAULT_RADIUS))
            ->get()
            ->each(fn (Vet $other) => $other->distance_km = $vet->latitude !== null ? $other->distanceFrom($vet->latitude, $vet->longitude) : null)
            ->filter(fn (Vet $other) => $other->distance_km !== null && $other->distance_km <= self::DEFAULT_RADIUS)
            ->sortBy('distance_km')
            ->take(3)
            ->values();

        return view('vets.show', ['vet' => $vet, 'nearby' => $others]);
    }

    /** Public "list your clinic" form. The listing is visible as soon as it is saved (an admin can hide it later). */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isDoctor()) {
            return view('providers.seller-only', ['what' => 'doctor']);
        }

        if ($request->user()->vet) {
            return redirect()->route('doctor.profile');
        }

        return view('vets.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(VetRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isDoctor(), 403, __('Only sellers can list a clinic.'));
        abort_if($user->vet, 409, __('You already have a clinic profile.'));

        $vet = Vet::create([
            ...$request->vetData(),
            'user_id' => $user->id,
            'slug' => $this->uniqueSlug($request->input('name').' '.$request->input('city')),
            'is_active' => true,
            'source' => 'user',
        ]);
        $vet->categories()->sync($request->input('categories', []));

        return redirect()->route('doctor.dashboard')->with('status', __('Your profile is live! People within 50 km can now find you in the doctor search.'));
    }

    private function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'vet';
        $slug = $base;

        for ($i = 2; Vet::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
