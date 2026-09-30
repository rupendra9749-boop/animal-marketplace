<?php

namespace App\Http\Controllers\Caretaker;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaretakerRequest;
use App\Models\Caretaker;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** A caretaker keeps their own service profile up to date here. The first save waits for admin approval. */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        $caretaker = $user->caretaker?->load('categories') ?? new Caretaker([
            'name' => $user->name,
            'phone' => $user->phone,
            'state' => $user->state,
            'city' => $user->city,
            'email' => $user->email,
            'available' => true,
            'rate_unit' => 'day',
        ]);

        return view('caretaker.profile', ['caretaker' => $caretaker, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(CaretakerRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $caretaker = $user->caretaker;
        $data = $request->caretakerData();

        if ($request->hasFile('photo')) {
            if ($caretaker?->photo_path) {
                Storage::disk('public')->delete($caretaker->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('caretakers', 'public');
        }

        if ($caretaker) {
            $caretaker->update($data);
            $message = __('Your profile was saved.');
        } else {
            $caretaker = Caretaker::create([
                ...$data,
                'user_id' => $user->id,
                'slug' => $this->uniqueSlug($data['name'].' '.$data['city']),
                'is_active' => true,
            ]);
            $message = __('Your profile is live! People within 50 km can now find you in the caretaker search.');
        }

        $caretaker->categories()->sync($request->input('categories', []));

        return redirect()->route('caretaker.dashboard')->with('status', $message);
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
