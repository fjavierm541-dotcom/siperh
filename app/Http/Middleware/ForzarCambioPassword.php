<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForzarCambioPassword
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            auth()->user()->debe_cambiar_password &&
            !$request->routeIs('password.cambiar') &&
            !$request->routeIs('password.actualizar') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('password.cambiar');
        }

        return $next($request);
    }
}