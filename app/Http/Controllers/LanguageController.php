<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    /** Switch the site between English and Hindi, remember it, and go back to the page the person was on. */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(array_key_exists($locale, config('app.locales')), 404);

        $request->session()->put('locale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365);

        if ($user = $request->user()) {
            $user->forceFill(['locale' => $locale])->save();
        }

        return back(fallback: route('home'));
    }
}
