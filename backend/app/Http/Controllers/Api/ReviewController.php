<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'reviewee_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'order_id' => $request->order_id,
            'reviewer_id' => Auth::guard('api')->id(),
            'reviewee_id' => $request->reviewee_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Review submitted', 'review' => $review], 201);
    }

    public function userReviews($userId)
    {
        return response()->json(
            Review::with('reviewer')->where('reviewee_id', $userId)->latest()->get()
        );
    }
}
