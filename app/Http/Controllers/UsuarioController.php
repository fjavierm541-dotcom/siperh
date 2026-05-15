<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('empleado')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        // Por ahora cargamos empleados activos.
        // Luego afinamos el filtro: RRHH + jefes de departamento.
        $empleados = Empleado::orderBy('primer_nombre')
            ->get();

        return view('usuarios.create', compact('empleados'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'username' => strtolower(trim($request->username)),
        ]);

        $request->validate([
            'empleado_dni' => 'required|string|max:255|exists:empleados,DNI|unique:users,empleado_dni',

            'name' => 'required|string|max:150',

            'username' => [
                'required',
                'string',
                'max:50',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/',
            ],

            'email' => 'nullable|email:rfc,dns|max:150|unique:users,email',

            'telefono' => [
                'nullable',
                'regex:/^[2389][0-9]{7}$/',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'rol' => 'required|in:superadmin,rrhh,jefe_departamento',
        ], [
            'empleado_dni.required' => 'Debe seleccionar un empleado.',
            'empleado_dni.exists' => 'El empleado seleccionado no existe.',
            'empleado_dni.unique' => 'Este empleado ya tiene un usuario asignado.',

            'name.required' => 'El nombre visible es obligatorio.',

            'username.required' => 'El usuario es obligatorio.',
            'username.unique' => 'Este nombre de usuario ya está en uso.',
            'username.regex' => 'El usuario no debe tener espacios ni iniciar con símbolos. Solo use letras, números, punto, guion bajo o guion medio.',

            'email.email' => 'Ingrese un correo válido.',
            'email.unique' => 'Este correo ya está registrado.',

            'telefono.regex' => 'El teléfono debe tener 8 dígitos e iniciar con 2, 3, 8 o 9.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',

            'rol.required' => 'Debe seleccionar un rol.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        $usuarioLogueado = auth()->user();

        if (
            $usuarioLogueado->rol === 'rrhh' &&
            in_array($request->rol, ['superadmin', 'rrhh'])
        ) {
            abort(403, 'No tienes permiso para crear este tipo de usuario.');
        }

        if ($usuarioLogueado->rol === 'jefe_departamento') {
            abort(403, 'No tienes permiso para crear usuarios.');
        }

        User::create([
            'empleado_dni' => $request->empleado_dni,
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => $request->password,
            'rol' => $request->rol,
            'activo' => true,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /* ==========================
   ACTIVAR / DESACTIVAR
========================== */

public function toggle($id)
{
    $usuario = User::findOrFail($id);

    // Evitar desactivar su propio usuario
    if ($usuario->id === auth()->id()) {

        return back()->with(
            'error',
            'No puedes desactivar tu propio usuario.'
        );

    }

    $usuario->activo = !$usuario->activo;

    $usuario->save();

    return back()->with(
        'success',
        'Estado del usuario actualizado correctamente.'
    );
}

/* ==========================
   FORM EDITAR
========================== */

public function edit($id)
{
    $usuario = User::findOrFail($id);

    $empleados = Empleado::orderBy('primer_nombre')
        ->get();

    return view(
        'usuarios.edit',
        compact('usuario', 'empleados')
    );
}

/* ==========================
   ACTUALIZAR USUARIO
========================== */

public function update(Request $request, $id)
{
    $usuario = User::findOrFail($id);

    $request->merge([
        'username' => strtolower(trim($request->username)),
    ]);

    $request->validate([

        'empleado_dni' =>
            'required|string|max:255|exists:empleados,DNI|unique:users,empleado_dni,' . $usuario->id,

        'name' =>
            'required|string|max:150',

        'username' => [
            'required',
            'string',
            'max:50',
            'unique:users,username,' . $usuario->id,
            'regex:/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/',
        ],

        'email' =>
            'nullable|email:rfc,dns|max:150|unique:users,email,' . $usuario->id,

        'telefono' => [
            'nullable',
            'regex:/^[2389][0-9]{7}$/',
        ],

        'rol' =>
            'required|in:superadmin,rrhh,jefe_departamento',

    ]);

    $usuario->update([

        'empleado_dni' => $request->empleado_dni,
        'name' => $request->name,
        'username' => $request->username,
        'email' => $request->email,
        'telefono' => $request->telefono,
        'rol' => $request->rol,

    ]);

    return redirect()
        ->route('usuarios.index')
        ->with(
            'success',
            'Usuario actualizado correctamente.'
        );
}

public function resetPassword($id)
{
    $usuario = User::findOrFail($id);

    /* ==========================
       GENERAR PASSWORD SEGURA
    ========================== */

    $mayusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $minusculas = 'abcdefghijkmnopqrstuvwxyz';
    $numeros = '23456789';
    $simbolos = '@#$%&*';

    // Garantizar requisitos mínimos
    $password =
        $mayusculas[random_int(0, strlen($mayusculas) - 1)] .
        $minusculas[random_int(0, strlen($minusculas) - 1)] .
        $numeros[random_int(0, strlen($numeros) - 1)] .
        $simbolos[random_int(0, strlen($simbolos) - 1)];

    // Completar hasta 8 caracteres
    $todos = $mayusculas . $minusculas . $numeros . $simbolos;

    for ($i = 0; $i < 4; $i++) {

        $password .= $todos[random_int(0, strlen($todos) - 1)];

    }

    // Mezclar caracteres
    $nuevaPassword = str_shuffle($password);

    /* ==========================
       GUARDAR PASSWORD
    ========================== */

    $usuario->password = $nuevaPassword;

    // Obligar cambio
    $usuario->debe_cambiar_password = true;

    $usuario->save();

    return back()->with(
        'success',
        'Contraseña restablecida correctamente. Nueva contraseña temporal: ' . $nuevaPassword
    );
}
}