<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnimalController extends Controller
{
    public function index(Request $request): View
    {
        $animals = Animal::with(['seller', 'category'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->string('listing')->toString() === 'sale', fn ($query) => $query->forSale())
            ->when($request->string('listing')->toString() === 'breeding', fn ($query) => $query->forBreeding())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.animals.index', compact('animals'));
    }

    public function show(Animal $animal): View
    {
        $animal->load(['seller', 'category', 'orderItems.order.buyer']);

        return view('admin.animals.show', compact('animal'));
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $animal->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('status', __('":name" was updated.', ['name' => $animal->name]));
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        if ($animal->image_path) {
            Storage::disk('public')->delete($animal->image_path);
        }

        $animal->delete();

        return back()->with('status', __('Animal listing deleted.'));
    }
}
