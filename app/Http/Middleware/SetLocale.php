<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * The site speaks English and Hindi. Which one a person sees is decided in this order:
 *   1. what they picked in this browser session,
 *   2. what they saved on their account (so it follows them to another phone),
 *   3. the choice remembered in a cookie,
 *   4. the language their phone or browser asks for (a Hindi phone starts in Hindi),
 *   5. English.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->choose($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale); // month and day names

        return $next($request);
    }

    private function choose(Request $request): string
    {
        $supported = array_keys(config('app.locales'));

        $candidates = [
            $request->hasSession() ? $request->session()->get('locale') : null,
            $request->user()?->locale,
            $request->cookie('locale'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        if ($request->headers->has('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($supported);
            if (in_array($preferred, $supported, true)) {
                return $preferred;
            }
        }

        return config('app.locale');
    }
}
