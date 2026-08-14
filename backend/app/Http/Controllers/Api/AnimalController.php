<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'breed_id' => 'required|exists:breeds,id',
            'gender' => 'required|in:male,female',
            'price' => 'required|numeric',
            'images' => 'required|array|min:1',
            'images.*' => 'image|max:5120',
        ]);

        $animal = Animal::create([
            'user_id' => Auth::guard('api')->id(),
            'category_id' => $request->category_id,
            'breed_id' => $request->breed_id,
            'gender' => $request->gender,
            'age_years' => $request->age_years,
            'age_months' => $request->age_months,
            'weight' => $request->weight,
            'color' => $request->color,
            'health_status' => $request->health_status ?? 'not_vaccinated',
            'price' => $request->price,
            'is_negotiable' => $request->is_negotiable ?? 0,
            'description' => $request->description,
            'purpose' => $request->purpose,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'status' => 'available',
        ]);

        foreach ($request->file('images', []) as $index => $image) {
            $path = $image->store('animals', 'public');

            AnimalImage::create([
                'animal_id' => $animal->id,
                'image_path' => $path,
                'is_primary' => $index === 0 ? 1 : 0,
            ]);
        }

        return response()->json([
            'message' => 'Listing created successfully',
            'animal' => $animal->load('images', 'category', 'breed'),
        ], 201);
    }

    public function myListings()
    {
        echo "dddddd";
        die;
        $animals = Animal::with('images', 'category', 'breed')
            ->where('user_id', Auth::guard('api')->id())
            ->latest()
            ->get();

        return response()->json($animals);
    }

    public function index(Request $request)
    {
        $query = Animal::with('images', 'category', 'breed', 'user')
            ->where('status', 'available');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->breed_id) {
            $query->where('breed_id', $request->breed_id);
        }
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        return response()->json($query->latest()->paginate(12));
    }

    public function show($id)
    {
        $animal = Animal::with('images', 'category', 'breed', 'user')->findOrFail($id);
        $animal->increment('views_count');

        return response()->json($animal);
    }

    public function update(Request $request, $id)
    {
        $animal = Animal::where('user_id', Auth::guard('api')->id())->findOrFail($id);
        $animal->update($request->only([
            'price', 'description', 'is_negotiable', 'status',
            'health_status', 'weight', 'color',
        ]));

        return response()->json(['message' => 'Listing updated', 'animal' => $animal]);
    }

    public function destroy($id)
    {
        $animal = Animal::where('user_id', Auth::guard('api')->id())->findOrFail($id);
        $animal->delete();

        return response()->json(['message' => 'Listing deleted']);
    }
}
