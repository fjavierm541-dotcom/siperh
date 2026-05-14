<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar sesión
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Verificar si el rol del usuario está permitido
        if (!in_array(auth()->user()->rol, $roles)) {
            abort(403, 'No tienes permiso para acceder.');
        }

        return $next($request);
    }
}