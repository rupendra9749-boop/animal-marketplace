<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaretakerRequest;
use App\Models\Caretaker;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CaretakerController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $caretakers = Caretaker::query()
            ->with(['categories', 'user'])
            ->when($status === 'pending', fn ($q) => $q->where('is_active', false))
            ->when($status === 'live', fn ($q) => $q->where('is_active', true))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('city', 'like', $term)->orWhere('state', 'like', $term));
            })
            ->orderBy('is_active')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.caretakers.index', [
            'caretakers' => $caretakers,
            'status' => $status,
            'counts' => ['all' => Caretaker::count(), 'pending' => Caretaker::where('is_active', false)->count(), 'live' => Caretaker::where('is_active', true)->count()],
        ]);
    }

    public function create(): View
    {
        return view('admin.caretakers.create', ['caretaker' => new Caretaker(['is_active' => true, 'available' => true, 'rate_unit' => 'day']), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(CaretakerRequest $request): RedirectResponse
    {
        $caretaker = Caretaker::create([
            ...$request->caretakerData(),
            'slug' => $this->uniqueSlug($request->input('name').' '.$request->input('city')),
            'is_active' => $request->boolean('is_active'),
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('caretakers', 'public') : null,
        ]);
        $caretaker->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.caretakers.index')->with('status', __(':name was added.', ['name' => $caretaker->name]));
    }

    public function edit(Caretaker $caretaker): View
    {
        $caretaker->load('categories');

        return view('admin.caretakers.edit', ['caretaker' => $caretaker, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(CaretakerRequest $request, Caretaker $caretaker): RedirectResponse
    {
        $data = [...$request->caretakerData(), 'is_active' => $request->boolean('is_active')];

        if ($request->hasFile('photo')) {
            if ($caretaker->photo_path) {
                Storage::disk('public')->delete($caretaker->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('caretakers', 'public');
        }

        $caretaker->update($data);
        $caretaker->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.caretakers.index')->with('status', __(':name was updated.', ['name' => $caretaker->name]));
    }

    /** One-click approve / hide from the list. */
    public function toggle(Caretaker $caretaker): RedirectResponse
    {
        $caretaker->update(['is_active' => ! $caretaker->is_active]);

        return back()->with('status', $caretaker->is_active ? __(':name is now visible to buyers.', ['name' => $caretaker->name]) : __(':name is now hidden.', ['name' => $caretaker->name]));
    }

    public function destroy(Caretaker $caretaker): RedirectResponse
    {
        if ($caretaker->photo_path) {
            Storage::disk('public')->delete($caretaker->photo_path);
        }
        $caretaker->delete();

        return redirect()->route('admin.caretakers.index')->with('status', __('Caretaker removed.'));
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
