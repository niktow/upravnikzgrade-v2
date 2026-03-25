<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenant
{
    /**
     * Handle an incoming request.
     * Dozvoljava pristup samo stanarima (tenant role).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isTenant()) {
            // Ako je upravnik/admin, preusmeri na admin panel
            if (auth()->user()->isManager()) {
                return redirect('/admin');
            }
            
            abort(403, 'Pristup dozvoljen samo stanarima.');
        }

        return $next($request);
    }
}
