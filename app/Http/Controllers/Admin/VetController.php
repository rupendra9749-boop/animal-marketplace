<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VetRequest;
use App\Models\AiSearch;
use App\Models\Category;
use App\Models\Vet;
use App\Services\AiVetFinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VetController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $vets = Vet::query()
            ->with(['categories', 'submittedBy'])
            ->when($status === 'pending', fn ($q) => $q->where('is_active', false))
            ->when($status === 'live', fn ($q) => $q->where('is_active', true))
            ->when($status === 'ai', fn ($q) => $q->where('source', 'ai'))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('clinic_name', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->orderBy('is_active')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.vets.index', [
            'vets' => $vets,
            'status' => $status,
            'counts' => [
                'all' => Vet::count(),
                'pending' => Vet::where('is_active', false)->count(),
                'live' => Vet::where('is_active', true)->count(),
                'ai' => Vet::where('source', 'ai')->count(),
            ],
            'aiConfigured' => app(AiVetFinder::class)->isConfigured(),
            'aiSearches' => AiSearch::with(['user', 'category'])->where('kind', 'vet')->latest()->take(6)->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vets.create', ['vet' => new Vet(['is_active' => true]), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(VetRequest $request): RedirectResponse
    {
        $vet = Vet::create([
            ...$request->vetData(),
            'slug' => $this->uniqueSlug($request->input('name').' '.$request->input('city')),
            'is_active' => $request->boolean('is_active'),
            'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('vets', 'public') : null,
        ]);
        $vet->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.vets.index')->with('status', __(':name was added.', ['name' => $vet->name]));
    }

    public function edit(Vet $vet): View
    {
        $vet->load('categories');

        return view('admin.vets.edit', ['vet' => $vet, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(VetRequest $request, Vet $vet): RedirectResponse
    {
        $data = [...$request->vetData(), 'is_active' => $request->boolean('is_active')];

        if ($request->hasFile('photo')) {
            if ($vet->photo_path) {
                Storage::disk('public')->delete($vet->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('vets', 'public');
        }

        $vet->update($data);
        $vet->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.vets.index')->with('status', __(':name was updated.', ['name' => $vet->name]));
    }

    /** One-click approve / hide from the list. */
    public function toggle(Vet $vet): RedirectResponse
    {
        $vet->update(['is_active' => ! $vet->is_active]);

        return back()->with('status', $vet->is_active ? __(':name is now visible to buyers.', ['name' => $vet->name]) : __(':name is now hidden.', ['name' => $vet->name]));
    }

    /** An admin has checked an AI-found doctor (called, or looked at the source page). */
    public function verify(Vet $vet): RedirectResponse
    {
        $vet->update(['is_verified' => true]);

        return back()->with('status', __(':name is now marked as verified.', ['name' => $vet->name]));
    }

    /** Runs the AI search for any city, ignoring the daily limits that apply to buyers. */
    public function aiSearch(Request $request, AiVetFinder $finder): RedirectResponse
    {
        $data = $request->validate([
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        set_time_limit(100);
        $result = $finder->search($request->user(), $data['state'], $data['city'], isset($data['type']) ? Category::find($data['type']) : null, force: true);

        return redirect()->route('admin.vets.index', $result->found() ? ['status' => 'ai'] : [])->with('status', $result->message);
    }

    public function destroy(Vet $vet): RedirectResponse
    {
        if ($vet->photo_path) {
            Storage::disk('public')->delete($vet->photo_path);
        }
        $vet->delete();

        return redirect()->route('admin.vets.index')->with('status', __('Doctor removed.'));
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
