<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Locations;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SellerController extends Controller
{
    public function create(): View
    {
        return view('admin.sellers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => Phone::mobile($request->input('phone')) ?? $request->input('phone')]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'regex:/^\+91 [6-9]\d{4} \d{5}$/', 'unique:'.User::class],
            'state' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'phone.regex' => __('Enter a valid 10-digit Indian mobile number.'),
            'phone.unique' => __('This phone number is already registered.'),
        ]);

        if (! Locations::find($validated['state'], $validated['city'])) {
            throw ValidationException::withMessages(['city' => __('Please pick the city from the list.')]);
        }

        $seller = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_seller' => true,
        ]);
        $seller->setHomeCity($validated['state'], $validated['city']);
        $seller->save();

        return redirect()->route('admin.users.index')->with('status', __('Seller account created for :name.', ['name' => $validated['name']]));
    }
}
