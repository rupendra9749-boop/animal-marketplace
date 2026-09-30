<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers whether the user is in their buyer ("account") or seller area, so pages shared by
 * both (like Messages) can show the matching sidebar.
 */
class SetPanel
{
    public function handle(Request $request, Closure $next, string $panel): Response
    {
        $request->session()->put('panel', $panel);

        return $next($request);
    }
}
