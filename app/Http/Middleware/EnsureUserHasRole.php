<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $hasRole = $user && collect($roles)->contains(fn (string $role) => match ($role) {
            'admin' => $user->isAdmin(),
            'seller' => $user->isSeller(),
            'breeder' => $user->isBreeder(),
            'doctor' => $user->isDoctor(),
            'caretaker' => $user->isCaretaker(),
            default => false,
        });

        if (! $user || ! $user->is_active || ! $hasRole) {
            abort(403);
        }

        return $next($request);
    }
}
