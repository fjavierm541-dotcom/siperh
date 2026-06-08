<?php

namespace App\Http\Controllers;
use App\Models\Practicante;
use App\Models\Empleado;
use App\Models\PermisoPracticante;
use App\Models\TipoPermisoPracticante;
use App\Models\EstadoPermisoSistema;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PermisoPracticanteController extends Controller
{
public function create()
{
    $usuario = auth()->user();

    if ($usuario->rol === 'jefe_departamento') {

        $empleadoUsuario = Empleado::findOrFail(
            $usuario->empleado_dni
        );

        $practicantes = Practicante::where(
                'departamento_id',
                $empleadoUsuario->departamento_funcional_id
            )
            ->where('activo', 1)
            ->orderBy('nombre_completo')
            ->get();

    } else {

        $practicantes = Practicante::where('activo', 1)
            ->orderBy('nombre_completo')
            ->get();
    }

    $tipos = TipoPermisoPracticante::where(
            'activo',
            1
        )
        ->orderBy('nombre')
        ->get();

    return view(
        'permisos_practicantes.create',
        compact(
            'practicantes',
            'tipos'
        )
    );
}

public function index(Request $request)
{
    $permisos = PermisoPracticante::with([
            'practicante',
            'tipo',
            'estado'
        ])

        ->when($request->filled('buscar'),
            function ($query) use ($request) {

                $buscar = $request->buscar;

                $query->where(function ($q)
                use ($buscar) {

                    $q->whereHas(
                        'practicante',
                        function ($p)
                        use ($buscar) {

                            $p->where(
                                'nombre_completo',
                                'LIKE',
                                "%{$buscar}%"
                            )
                            ->orWhere(
                                'dni_practicante',
                                'LIKE',
                                "%{$buscar}%"
                            );
                        }
                    )

                    ->orWhereHas(
                        'tipo',
                        function ($tipo)
                        use ($buscar) {

                            $tipo->where(
                                'nombre',
                                'LIKE',
                                "%{$buscar}%"
                            );
                        }
                    );
                });
            })

        ->latest()

        ->paginate(15)

        ->withQueryString();

    return view(
        'permisos_practicantes.index',
        compact('permisos')
    );
}

public function store(Request $request)
{
    $request->validate([

        'practicante_id' => 'required|exists:practicantes,id',

        'tipo_permiso_id' => 'required|exists:tipos_permiso_practicantes,id',

        'modalidad' => 'required',

        'fecha_inicio' => 'required|date',

        'motivo' => 'nullable|string|max:500',

        'documento' => 'nullable|file|mimes:pdf|max:5120',

        'horas' => 'nullable|numeric|min:0|max:8',

        'hora_salida' => 'nullable|date_format:H:i',

        'hora_entrada' => 'nullable|date_format:H:i',
    ]);

    if (
        $request->modalidad === 'varios_dias'
        && !empty($request->fecha_fin)
    ) {

        $fechaInicio = Carbon::parse(
            $request->fecha_inicio
        );

        $fechaFin = Carbon::parse(
            $request->fecha_fin
        );

        if ($fechaFin->lt($fechaInicio)) {

            return back()
                ->withErrors([
                    'fecha_fin' =>
                        'La fecha final no puede ser menor que la fecha inicial.'
                ])
                ->withInput();
        }
    }

    $usuario = auth()->user();

    if ($usuario->rol === 'jefe_departamento') {

        $empleadoUsuario = Empleado::findOrFail(
            $usuario->empleado_dni
        );

        $practicante = Practicante::where(
                'id',
                $request->practicante_id
            )
            ->where(
                'departamento_id',
                $empleadoUsuario->departamento_funcional_id
            )
            ->first();

        if (!$practicante) {

            abort(
                403,
                'No puedes crear permisos para practicantes de otro departamento.'
            );
        }

    } else {

        $practicante = Practicante::findOrFail(
            $request->practicante_id
        );
    }

    $rutaDocumento = null;

    if ($request->hasFile('documento')) {

        $rutaDocumento = $request
            ->file('documento')
            ->store(
                'documentos_permisos_practicantes',
                'public'
            );
    }

    $horasDecimal = 0;

    if ($request->modalidad === 'horas') {

        if (
            !$request->hora_salida
            || !$request->hora_entrada
        ) {

            return back()
                ->withErrors([
                    'hora_salida' =>
                        'Debe ingresar hora de salida y hora de entrada.'
                ])
                ->withInput();
        }

        $salida = Carbon::createFromFormat(
            'H:i',
            $request->hora_salida
        );

        $entrada = Carbon::createFromFormat(
            'H:i',
            $request->hora_entrada
        );

        if (
            $entrada->lessThanOrEqualTo($salida)
        ) {

            return back()
                ->withErrors([
                    'hora_entrada' =>
                        'La hora de entrada debe ser mayor que la hora de salida.'
                ])
                ->withInput();
        }

        $minutosTotales =
            $salida->diffInMinutes($entrada);

        if ($minutosTotales > 480) {

            return back()
                ->withErrors([
                    'hora_entrada' =>
                        'El permiso no puede superar 8 horas.'
                ])
                ->withInput();
        }

        $horasDecimal =
            round(
                $minutosTotales / 60,
                2
            );
    }

    $permiso = PermisoPracticante::create([

        'practicante_id' =>
            $request->practicante_id,

        'tipo_permiso_id' =>
            $request->tipo_permiso_id,

        'estado_permiso_id' => 1,

        'modalidad' =>
            $request->modalidad,

        'fecha_inicio' =>
            $request->fecha_inicio,

        'fecha_fin' =>
            $request->fecha_fin,

        'horas' =>
            $horasDecimal,

        'hora_salida' =>
            $request->modalidad === 'horas'
                ? $request->hora_salida
                : null,

        'hora_entrada' =>
            $request->modalidad === 'horas'
                ? $request->hora_entrada
                : null,

        'motivo' =>
            $request->motivo,

        'documento' =>
            $rutaDocumento,
    ]);

    BitacoraSistema::create([

        'usuario_id'      => auth()->id(),
        'usuario_nombre'  => auth()->user()->name,
        'rol_usuario'     => auth()->user()->rol,
        'empleado_dni'    => auth()->user()->empleado_dni,

        'accion'          => 'Crear permiso practicante',

        'modulo'          => 'Permisos Practicantes',

        'descripcion'     =>
            'Se registró una nueva solicitud de permiso para el practicante: '
            . $practicante->nombre_completo,

        'ip_equipo'       => request()->ip(),

        'user_agent'      => request()->userAgent(),

        'metodo'          => request()->method(),

        'ruta'            => request()->path(),

        'referencia_id'   => $permiso->id,

        'referencia_tipo' => 'permiso_practicante',

        'valores_nuevos'  => $permiso->toArray(),

        'estado'          => 'Exitoso',
    ]);

   return redirect()
    ->route(
        auth()->user()->rol === 'jefe_departamento'
            ? 'permisos-practicantes.mis'
            : 'permisos-practicantes.index'
    )
    ->with([
        'success' => 'Solicitud enviada correctamente.',
        'permiso_imprimir' => $permiso->id
    ]);
}







public function misPermisos(Request $request)
{
    $usuario = auth()->user();

    $query = PermisoPracticante::with([
        'practicante',
        'tipo',
        'estado'
    ]);

    if ($usuario->rol === 'jefe_departamento') {

        $empleadoUsuario = Empleado::findOrFail(
            $usuario->empleado_dni
        );

        $query->whereHas(
            'practicante',
            function ($q)
            use ($empleadoUsuario) {

                $q->where(
                    'departamento_id',
                    $empleadoUsuario->departamento_funcional_id
                );
            }
        );
    }

    $permisos = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view(
        'permisos_practicantes.mis',
        compact('permisos')
    );
} 


public function cancelar($id)
{
    $permiso = PermisoPracticante::with([
        'estado',
        'practicante'
    ])->findOrFail($id);

    if (strtolower($permiso->estado->nombre) !== 'pendiente') {

        return back()->with(
            'error',
            'Solo se pueden cancelar permisos pendientes.'
        );
    }

    $estadoCancelado = EstadoPermisoSistema::whereRaw(
        'LOWER(nombre) = ?',
        ['cancelado']
    )->first();

    if (!$estadoCancelado) {

        return back()->with(
            'error',
            'No existe el estado Cancelado.'
        );
    }

    $permiso->estado_permiso_id =
        $estadoCancelado->id;

    $permiso->save();

    return back()->with(
        'success',
        'Permiso cancelado correctamente.'
    );
}

public function imprimir($id)
{
    $permiso = PermisoPracticante::with([
        'practicante.departamento',
        'tipo',
        'estado'
    ])->findOrFail($id);

    return view(
        'permisos_practicantes.imprimir',
        compact('permiso')
    );
}


public function aprobar($id)
{
    $permiso = PermisoPracticante::findOrFail($id);

    $estadoAprobado = EstadoPermisoSistema::whereRaw(
        'LOWER(nombre) = ?',
        ['aprobado']
    )->first();

    if (!$estadoAprobado) {

        return back()->with(
            'error',
            'No existe el estado Aprobado.'
        );
    }

    $permiso->estado_permiso_id =
        $estadoAprobado->id;

    $permiso->save();

    return back()->with(
        'success',
        'Permiso aprobado correctamente.'
    );
}


public function rechazar(
    Request $request,
    $id
)
{
    $request->validate([
        'motivo_rechazo' =>
            'required|string|max:500'
    ], [
        'motivo_rechazo.required' =>
            'Debe ingresar el motivo del rechazo.'
    ]);

    $permiso = PermisoPracticante::findOrFail($id);

    $estadoRechazado =
        EstadoPermisoSistema::whereRaw(
            'LOWER(nombre) = ?',
            ['rechazado']
        )->first();

    if (!$estadoRechazado) {

        return back()->with(
            'error',
            'No existe el estado Rechazado.'
        );
    }

    $permiso->estado_permiso_id =
        $estadoRechazado->id;

   $permiso->motivo_rechazo =
    $request->motivo_rechazo;

    $permiso->save();

    return back()->with(
        'success',
        'Permiso rechazado correctamente.'
    );
}

}
