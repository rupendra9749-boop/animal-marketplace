<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Category;
use App\Support\Locations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnimalController extends Controller
{
    /** Which area this controller serves ("seller" or "breeder"); used for route and view names. */
    protected string $area = 'seller';

    /** Breeders may only list animals for breeding. */
    protected bool $breedingOnly = false;

    public function index(): View
    {
        $animals = Auth::user()->animals()
            ->when($this->breedingOnly, fn ($query) => $query->forBreeding())
            ->latest()
            ->paginate(10);

        return view('seller.animals.index', ['animals' => $animals, 'area' => $this->area]);
    }

    public function show(Animal $animal): View
    {
        $this->authorize('update', $animal);

        $animal->load('category');

        return view('seller.animals.show', ['animal' => $animal, 'area' => $this->area]);
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.animals.create', ['categories' => $categories, 'area' => $this->area]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Animal::class);

        $validated = $this->validated($request);

        $animal = Auth::user()->animals()->create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['name']),
            'image_path' => $this->storeImage($request),
        ]);

        return redirect()->route($this->area.'.animals.index')->with('status', __('":name" was created.', ['name' => $animal->name]));
    }

    public function edit(Animal $animal): View
    {
        $this->authorize('update', $animal);

        $categories = Category::orderBy('name')->get();

        return view('seller.animals.edit', ['animal' => $animal, 'categories' => $categories, 'area' => $this->area]);
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $this->authorize('update', $animal);

        $validated = $this->validated($request);

        if ($image = $this->storeImage($request)) {
            if ($animal->image_path) {
                Storage::disk('public')->delete($animal->image_path);
            }
            $validated['image_path'] = $image;
        }

        $animal->update($validated);

        return redirect()->route($this->area.'.animals.index')->with('status', __('":name" was updated.', ['name' => $animal->name]));
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        $this->authorize('delete', $animal);

        if ($animal->image_path) {
            Storage::disk('public')->delete($animal->image_path);
        }

        $animal->delete();

        return redirect()->route($this->area.'.animals.index')->with('status', __('Animal listing deleted.'));
    }

    protected function validated(Request $request): array
    {
        if ($this->breedingOnly) {
            $request->merge(['listing_type' => Animal::TYPE_BREEDING]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'breed' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female,unknown'],
            'color' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'listing_type' => ['nullable', Rule::in(Animal::LISTING_TYPES)],
            'price' => ['required_unless:listing_type,breeding', 'nullable', 'numeric', 'min:0'],
            'breeding_fee' => ['required_if:listing_type,breeding', 'nullable', 'numeric', 'min:0'],
            'stock' => ['required_unless:listing_type,breeding', 'nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['listing_type'] = $validated['listing_type'] ?? Animal::TYPE_SALE;

        if ($validated['listing_type'] === Animal::TYPE_BREEDING) {
            // Breeding-only animals are never bought, so they carry no price or stock of their own.
            $validated['price'] = 0;
            $validated['stock'] = 1;
        } elseif ($validated['listing_type'] === Animal::TYPE_SALE) {
            $validated['breeding_fee'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_vaccinated'] = $request->boolean('is_vaccinated');

        // The animal is placed on the map from its state and city, so nearby buyers can find it.
        $place = Locations::find($validated['state'], $validated['city']);
        if (! $place) {
            throw ValidationException::withMessages(['city' => __('Please pick the city from the list.')]);
        }

        unset($validated['city']);
        $validated['state'] = $place['state'];
        $validated['location'] = $place['city'];
        $validated['latitude'] = $place['lat'];
        $validated['longitude'] = $place['lng'];

        return $validated;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('animals', 'public');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Animal::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
