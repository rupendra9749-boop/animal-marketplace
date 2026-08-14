<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use Illuminate\Http\Request;

class BreedController extends Controller
{
    public function index(Request $request)
    {
        $query = Breed::where('status', 1);
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        return response()->json($query->get());
    }
}
