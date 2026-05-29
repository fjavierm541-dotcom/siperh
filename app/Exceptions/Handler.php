<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /** 
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        /*
        |--------------------------------------------------------------------------
        | ERROR 419 - SESIÓN EXPIRADA
        |--------------------------------------------------------------------------
        |
        | Cuando expire el token CSRF:
        | - cerrar sesión completamente
        | - invalidar sesión
        | - regenerar token
        | - redirigir al login
        |
        */

        $this->renderable(function (TokenMismatchException $e, $request) {

            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Tu sesión expiró por inactividad. Inicia sesión nuevamente.'
                );

        });
    }
}