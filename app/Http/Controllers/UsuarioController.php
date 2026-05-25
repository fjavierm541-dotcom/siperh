<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use App\Helpers\BitacoraHelper;
use App\Helpers\NotificacionHelper;

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
    // Empleados que ya tienen usuario
    $empleadosConUsuario = User::whereNotNull('empleado_dni')
        ->pluck('empleado_dni')
        ->toArray();

    // DNIs de jefes de departamento
    $jefesDepartamento = DB::table('departamentos_muni')
        ->whereNotNull('jefe_dni')
        ->pluck('jefe_dni')
        ->toArray();

    $empleados = Empleado::with('departamentoFuncional')
        ->whereNotIn('DNI', $empleadosConUsuario)
        ->where(function ($query) use ($jefesDepartamento) {

            // Jefes de departamento
            $query->whereIn('DNI', $jefesDepartamento)

            // Personal de Recursos Humanos
            ->orWhereHas('departamentoFuncional', function ($q) {
                $q->where('nombre', 'like', '%recursos humanos%')
                  ->orWhere('nombre', 'like', '%rrhh%')
                  ->orWhere('nombre', 'like', '%recursos%');
            });

        })
        ->orderBy('primer_nombre')
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

        $empleado = Empleado::findOrFail($request->empleado_dni);

        $nombreCompleto = trim(
            $empleado->primer_nombre . ' ' .
            $empleado->segundo_nombre . ' ' .
            $empleado->primer_apellido . ' ' .
            $empleado->segundo_apellido
        );

        $usuarioNuevo = User::create([
            'empleado_dni' => $request->empleado_dni,
            'name' => $nombreCompleto,
            'username' => $request->username,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => $request->password,
            'rol' => $request->rol,
            'activo' => true,
        ]);

        BitacoraHelper::registrar(
        'crear_usuario',
        'usuarios',
        'Se creó un nuevo usuario: ' . $usuarioNuevo->username,
        $usuarioNuevo->id,
        'usuario',
        null,
        [
            'id' => $usuarioNuevo->id,
            'username' => $usuarioNuevo->username,
            'rol' => $usuarioNuevo->rol,
            'activo' => $usuarioNuevo->activo,
        ]
    );

        NotificacionHelper::crear(
        null,
        'rrhh',
        'Nuevo usuario creado',
        'Se creó el usuario ' . $usuarioNuevo->username . ' con rol ' . $usuarioNuevo->rol . '.',
        'success',
        'usuarios',
        route('usuarios.index')
        );

        NotificacionHelper::crear(
            null,
            'superadmin',
            'Nuevo usuario creado',
            'Se creó el usuario ' . $usuarioNuevo->username . ' con rol ' . $usuarioNuevo->rol . '.',
            'success',
            'usuarios',
            route('usuarios.index')
        );

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

   
    $estadoAnterior = $usuario->activo;

    $usuario->activo = !$usuario->activo;

    $usuario->save();

    BitacoraHelper::registrar(
        $usuario->activo ? 'activar_usuario' : 'desactivar_usuario',
        'usuarios',
        $usuario->activo
            ? 'Se activó el usuario: ' . $usuario->username
            : 'Se desactivó el usuario: ' . $usuario->username,
        $usuario->id,
        'usuario',
        [
            'activo' => $estadoAnterior
        ],
        [
            'activo' => $usuario->activo
        ]
    );

        $titulo = $usuario->activo
        ? 'Usuario activado'
        : 'Usuario desactivado';

    $mensaje = $usuario->activo
        ? 'Se activó el usuario ' . $usuario->username . '.'
        : 'Se desactivó el usuario ' . $usuario->username . '.';

    NotificacionHelper::crear(
        null,
        'rrhh',
        $titulo,
        $mensaje,
        $usuario->activo ? 'success' : 'warning',
        'usuarios',
        route('usuarios.index')
    );

    NotificacionHelper::crear(
        null,
        'superadmin',
        $titulo,
        $mensaje,
        $usuario->activo ? 'success' : 'warning',
        'usuarios',
        route('usuarios.index')
    );

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

    $datosAnteriores = $usuario->toArray();

    $usuario->update([

        'empleado_dni' => $request->empleado_dni,
        'name' => $request->name,
        'username' => $request->username,
        'email' => $request->email,
        'telefono' => $request->telefono,
        'rol' => $request->rol,

    ]);

    BitacoraHelper::registrar(
    'editar_usuario',
    'usuarios',
    'Se actualizó el usuario: ' . $usuario->username,
    $usuario->id,
    'usuario',
    $datosAnteriores,
    $usuario->fresh()->toArray()
);

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

    BitacoraHelper::registrar(
    'reset_password_usuario',
    'usuarios',
    'Se restableció la contraseña del usuario: ' . $usuario->username,
    $usuario->id,
    'usuario'
);

    return back()->with(
        'success',
        'Contraseña restablecida correctamente. Nueva contraseña temporal: ' . $nuevaPassword
    );
}
}