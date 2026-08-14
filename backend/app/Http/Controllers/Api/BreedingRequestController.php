<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BreedingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BreedingRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'breeding_profile_id' => 'required|exists:breeding_profiles,id',
            'message' => 'nullable|string',
        ]);

        $breedingRequest = BreedingRequest::create([
            'breeding_profile_id' => $request->breeding_profile_id,
            'requested_by' => Auth::guard('api')->id(),
            'status' => 'pending',
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Request sent', 'request' => $breedingRequest], 201);
    }

    public function respond(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:accepted,rejected']);

        $breedingRequest = BreedingRequest::findOrFail($id);
        $breedingRequest->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            $breedingRequest->breedingProfile()->update(['status' => 'matched']);
        }

        return response()->json(['message' => 'Request updated', 'request' => $breedingRequest]);
    }
}
