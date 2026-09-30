<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            // A buyer can buy and take services. A seller can also sell animals, list breeding animals and offer
            // a doctor and a caretaker profile.
            'is_seller' => $request->input('account_type') === 'seller',
        ]);
        // Emails to this person are written in the language they signed up in.
        $user->locale = app()->getLocale();
        $user->setHomeCity($request->input('state'), $request->input('city'));
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        // The city they just registered with is where they are, whatever they browsed as a guest.
        $request->session()->forget('location');

        return redirect(route('dashboard', absolute: false));
    }
}
