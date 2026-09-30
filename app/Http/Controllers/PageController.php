<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        $stats = [
            'animals' => Animal::where('is_active', true)->count(),
            'sellers' => User::where('is_seller', true)->count(),
            'cities' => Animal::whereNotNull('location')->distinct('location')->count('location'),
        ];

        return view('pages.about', compact('stats'));
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return back()->with('status', __('Thanks! Your message has been sent. Our team will get back to you soon.'));
    }
}
