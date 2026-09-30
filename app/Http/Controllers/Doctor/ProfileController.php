<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VetRequest;
use App\Models\Category;
use App\Models\Vet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** A doctor keeps their own clinic profile up to date here. The first save waits for admin approval. */
class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = Auth::user();

        $vet = $user->vet?->load('categories') ?? new Vet([
            'name' => $user->name,
            'phone' => $user->phone,
            'state' => $user->state,
            'city' => $user->city,
            'email' => $user->email,
        ]);

        return view('doctor.profile', ['vet' => $vet, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(VetRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $vet = $user->vet;
        $data = $request->vetData();

        if ($request->hasFile('photo')) {
            if ($vet?->photo_path) {
                Storage::disk('public')->delete($vet->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('vets', 'public');
        }

        if ($vet) {
            $vet->update($data);
            $message = __('Your profile was saved.');
        } else {
            $vet = Vet::create([
                ...$data,
                'user_id' => $user->id,
                'slug' => $this->uniqueSlug($data['name'].' '.$data['city']),
                'is_active' => true,
                'source' => 'user',
            ]);
            $message = __('Your profile is live! People within 50 km can now find you in the doctor search.');
        }

        $vet->categories()->sync($request->input('categories', []));

        return redirect()->route('doctor.dashboard')->with('status', $message);
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
