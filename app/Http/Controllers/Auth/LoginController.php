<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    public function mostrarLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $credenciales = [
            'username' => $request->username,
            'password' => $request->password,
            'activo' => 1,
        ];

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            //return redirect()->route('inicio');
            return redirect()->route('paginas.inicio');
        }

        return back()
            ->withErrors([
                'username' => 'Usuario o contraseña incorrectos.',
            ])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function mostrarCambiarPassword()
{
    return view('auth.cambiar-password');
}

public function actualizarPassword(Request $request)
{
    $request->validate([
        'password' => [
            'required',
            'confirmed',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols(),
        ],
    ], [
        'password.required' => 'La nueva contraseña es obligatoria.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
    ]);

    $user = auth()->user();

    $user->password = $request->password;
    $user->debe_cambiar_password = false;
    $user->save();

    return redirect()
        ->route('paginas.inicio')
        ->with('success', 'Contraseña actualizada correctamente.');
}

public function mantenerSesion(Request $request)
{
    return response()->json([
        'ok' => true,
        'message' => 'Sesión actualizada correctamente.',
    ]);
}
}