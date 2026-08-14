<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BreedingProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BreedingProfileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'breed_id' => 'required|exists:breeds,id',
            'gender_required' => 'required|in:male,female',
        ]);

        $profile = BreedingProfile::create([
            'user_id' => Auth::guard('api')->id(),
            'animal_id' => $request->animal_id,
            'breed_id' => $request->breed_id,
            'gender_required' => $request->gender_required,
            'age_range' => $request->age_range,
            'pedigree_details' => $request->pedigree_details,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Breeding profile created', 'profile' => $profile], 201);
    }

    // Suggested matches: same breed + opposite gender + active
    public function index(Request $request)
    {
        $query = BreedingProfile::with('animal', 'breed', 'user')->where('status', 'active');

        if ($request->breed_id) {
            $query->where('breed_id', $request->breed_id);
        }
        if ($request->gender_required) {
            $query->where('gender_required', $request->gender_required);
        }

        return response()->json($query->latest()->get());
    }

    public function show($id)
    {
        return response()->json(BreedingProfile::with('animal', 'breed', 'user')->findOrFail($id));
    }
}
