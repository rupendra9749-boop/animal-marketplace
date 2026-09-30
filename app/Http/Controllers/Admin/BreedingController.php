<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin's view of the breeding module: every animal offered for breeding, who offers it, and the numbers. */
class BreedingController extends Controller
{
    public function index(Request $request): View
    {
        $base = Animal::query()->forBreeding();

        $stats = [
            'total' => (clone $base)->count(),
            'live' => (clone $base)->where('is_active', true)->count(),
            'studs' => (clone $base)->where('gender', 'male')->count(),
            'dams' => (clone $base)->where('gender', 'female')->count(),
            'breeders' => User::where('is_breeder', true)->count(),
            'average_fee' => (float) (clone $base)->where('breeding_fee', '>', 0)->avg('breeding_fee'),
        ];

        $animals = Animal::query()
            ->forBreeding()
            ->with(['seller', 'category'])
            ->when($request->filled('sex') && in_array($request->string('sex')->toString(), ['male', 'female'], true), fn ($q) => $q->where('gender', $request->string('sex')->toString()))
            ->when($request->filled('type'), fn ($q) => $q->where('category_id', $request->integer('type')))
            ->when($request->string('status')->toString() === 'hidden', fn ($q) => $q->where('is_active', false))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('breed', 'like', $term)->orWhere('location', 'like', $term));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.breeding.index', [
            'animals' => $animals,
            'stats' => $stats,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
