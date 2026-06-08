<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Practicante;
use App\Models\Empleado;
use App\Models\DepartamentoMuni;
use App\Models\BitacoraSistema;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PracticanteController extends Controller
{
    public function index(Request $request)
{
    $usuario = auth()->user();

    $query = Practicante::with('departamento');

    // Jefes solo ven practicantes de su departamento
    if ($usuario->rol === 'jefe_departamento') {

        $empleadoUsuario = Empleado::findOrFail(
            $usuario->empleado_dni
        );

        $query->where(
            'departamento_id',
            $empleadoUsuario->departamento_funcional_id
        );
    }

    // Búsqueda
    if ($request->filled('buscar')) {

        $busqueda = trim($request->buscar);

        $query->where(function ($q) use ($busqueda) {

            $q->where(
                    'nombre_completo',
                    'like',
                    "%{$busqueda}%"
                )
                ->orWhere(
                    'dni_practicante',
                    'like',
                    "%{$busqueda}%"
                )
                ->orWhere(
                    'institucion',
                    'like',
                    "%{$busqueda}%"
                );
        });
    }

    $practicantes = $query
        ->orderBy('activo', 'desc')
        ->orderBy('nombre_completo')
        ->paginate(10)
        ->withQueryString();

    // Instituciones para imprimir
    $instituciones = Practicante::query()

        ->select('institucion')

        ->whereNotNull('institucion')

        ->where('institucion', '!=', '')

        ->distinct()

        ->orderBy('institucion')

        ->pluck('institucion');

    // Años para imprimir
    $anios = Practicante::query()

        ->selectRaw('YEAR(fecha_inicio) as anio')

        ->distinct()

        ->orderByDesc('anio')

        ->pluck('anio');

    return view(
        'practicantes.index',
        compact(
            'practicantes',
            'instituciones',
            'anios'
        )
    );
}

    public function create()
    {
        $departamentos = DepartamentoMuni::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view(
            'practicantes.create',
            compact('departamentos')
        );
    }





public function store(Request $request)
{
    $request->validate([

        'nombre_completo' => [
            'required',
            'string',
            'min:3',
            'max:255'
        ],

        'dni_practicante' => [
            'nullable',
            'digits:13'
        ],

        'institucion' => [
            'required',
            'string',
            'min:3',
            'max:255'
        ],

        'correo' => [
            'nullable',
            'email',
            'max:255'
        ],

        'horas_requeridas' => [
            'nullable',
            'integer',
            'min:1'
        ],

        'fecha_inicio' => [
            'required',
            'date'
        ],

        'fecha_fin' => [
            'nullable',
            'date',
            'after_or_equal:fecha_inicio'
        ],

        'departamento_id' => [
            'required',
            'exists:departamentos_muni,id'
        ],

    ], [

        'nombre_completo.required' =>
            'Debe ingresar el nombre completo.',

        'nombre_completo.min' =>
            'El nombre debe tener al menos 3 caracteres.',

        'nombre_completo.max' =>
            'El nombre no puede superar 255 caracteres.',

        'dni_practicante.digits' =>
            'La identidad debe contener exactamente 13 dígitos.',

        'institucion.required' =>
            'Debe ingresar la institución.',

        'institucion.min' =>
            'La institución debe tener al menos 3 caracteres.',

        'correo.email' =>
            'Debe ingresar un correo electrónico válido.',

        'horas_requeridas.integer' =>
            'Las horas requeridas deben ser un número entero.',

        'horas_requeridas.min' =>
            'Las horas requeridas deben ser mayores que cero.',

        'fecha_inicio.required' =>
            'Debe seleccionar la fecha de inicio.',

        'fecha_fin.after_or_equal' =>
            'La fecha final no puede ser menor que la fecha inicial.',

        'departamento_id.required' =>
            'Debe seleccionar un departamento.',

        'departamento_id.exists' =>
            'El departamento seleccionado no es válido.',
    ]);

    $practicante = Practicante::create([

        'nombre_completo'  => $request->nombre_completo,
        'dni_practicante'  => $request->dni_practicante,
        'institucion'      => $request->institucion,
        'correo'           => $request->correo,
        'horas_requeridas' => $request->horas_requeridas,
        'fecha_inicio'     => $request->fecha_inicio,
        'fecha_fin'        => $request->fecha_fin,
        'departamento_id'  => $request->departamento_id,
        'activo'           => true,

    ]);

    BitacoraSistema::create([

        'usuario_id'       => auth()->id(),
        'usuario_nombre'   => auth()->user()->name,
        'rol_usuario'      => auth()->user()->rol,
        'empleado_dni'     => auth()->user()->empleado_dni,

        'accion'           => 'Crear practicante',
        'modulo'           => 'Practicantes',

        'descripcion'      =>
            'Se registró un nuevo practicante: '
            . $practicante->nombre_completo,

        'ip_equipo'        => request()->ip(),
        'user_agent'       => request()->userAgent(),

        'metodo'           => request()->method(),
        'ruta'             => request()->path(),

        'referencia_id'    => $practicante->id,
        'referencia_tipo'  => 'practicante',

        'valores_nuevos'   => $practicante->toArray(),

        'estado'           => 'Exitoso',
    ]);

    return redirect()
        ->route('practicantes.index')
        ->with(
            'success',
            'Practicante registrado correctamente.'
        );
}

    public function toggle(Practicante $practicante)
    {
        $practicante->activo = !$practicante->activo;

        $practicante->save();

        BitacoraSistema::create([
            'usuario_id'       => auth()->id(),
            'usuario_nombre'   => auth()->user()->name,
            'rol_usuario'      => auth()->user()->rol,
            'empleado_dni'     => auth()->user()->empleado_dni,

            'accion' => $practicante->activo
                ? 'Activar practicante'
                : 'Inactivar practicante',

            'modulo' => 'Practicantes',

            'descripcion' => $practicante->activo
                ? 'Se activó el practicante: ' . $practicante->nombre_completo
                : 'Se inactivó el practicante: ' . $practicante->nombre_completo,

            'ip_equipo'        => request()->ip(),
            'user_agent'       => request()->userAgent(),

            'metodo'           => request()->method(),
            'ruta'             => request()->path(),

            'referencia_id'    => $practicante->id,
            'referencia_tipo'  => 'practicante',

            'valores_nuevos'   => [
                'activo' => $practicante->activo
            ],

            'estado'           => 'Exitoso',
        ]);

        return back()->with(
            'success',
            $practicante->activo
                ? 'Practicante activado correctamente.'
                : 'Practicante inactivado correctamente.'
        );
    }

    public function imprimir($tipo)
{
    $query = Practicante::with('departamento');

    switch ($tipo) {

        case 'activos':

            $query->where('activo', 1);

            $titulo = 'LISTADO DE PRACTICANTES ACTIVOS';

            break;

        case 'inactivos':

            $query->where('activo', 0);

            $titulo = 'LISTADO DE PRACTICANTES INACTIVOS';

            break;

        default:

            $titulo = 'LISTADO GENERAL DE PRACTICANTES';

            break;
    }

    $practicantes = $query
        ->orderBy('nombre_completo')
        ->get();

    // Bitácora
    BitacoraSistema::create([

        'usuario_id'      => auth()->id(),
        'usuario_nombre'  => auth()->user()->name,
        'rol_usuario'     => auth()->user()->rol,
        'empleado_dni'    => auth()->user()->empleado_dni,

        'accion' => match($tipo) {
            'activos'   => 'Imprimir practicantes activos',
            'inactivos' => 'Imprimir practicantes inactivos',
            default     => 'Imprimir todos los practicantes'
        },

        'modulo' => 'Practicantes',

        'descripcion' => match($tipo) {
            'activos'   => 'Se imprimió el listado de practicantes activos.',
            'inactivos' => 'Se imprimió el listado de practicantes inactivos.',
            default     => 'Se imprimió el listado general de practicantes.'
        },

        'ip_equipo'       => request()->ip(),
        'user_agent'      => request()->userAgent(),

        'metodo'          => request()->method(),
        'ruta'            => request()->path(),

        'referencia_tipo' => 'practicante',

        'estado'          => 'Exitoso',
    ]);

    $pdf = Pdf::loadView(
    'practicantes.listado',
    compact(
        'practicantes',
        'titulo'
    )
);

    return $pdf->stream(
        strtolower(str_replace(' ', '_', $titulo)) . '.pdf'
    );
}

public function edit(Practicante $practicante)
{
    $departamentos = DepartamentoMuni::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    return view(
        'practicantes.edit',
        compact(
            'practicante',
            'departamentos'
        )
    ); 
} 

public function update(
    Request $request,
    Practicante $practicante
)
{
    $request->validate([

        'nombre_completo' =>
            'required|string|max:255',

        'dni_practicante' =>
            'nullable|string|max:20',

        'institucion' =>
            'required|string|max:255',

        'correo' =>
            'nullable|email|max:255',

        'horas_requeridas' =>
            'nullable|integer|min:1',

        'fecha_inicio' =>
            'required|date',

        'fecha_fin' =>
            'nullable|date|after_or_equal:fecha_inicio',

        'departamento_id' =>
            'required|exists:departamentos_muni,id',
    ]);

    $valoresAnteriores =
        $practicante->toArray();

    $practicante->update([

        'nombre_completo' =>
            $request->nombre_completo,

        'dni_practicante' =>
            $request->dni_practicante,

        'institucion' =>
            $request->institucion,

        'correo' =>
            $request->correo,

        'horas_requeridas' =>
            $request->horas_requeridas,

        'fecha_inicio' =>
            $request->fecha_inicio,

        'fecha_fin' =>
            $request->fecha_fin,

        'departamento_id' =>
            $request->departamento_id,
    ]);

    BitacoraSistema::create([

        'usuario_id'      => auth()->id(),
        'usuario_nombre'  => auth()->user()->name,
        'rol_usuario'     => auth()->user()->rol,
        'empleado_dni'    => auth()->user()->empleado_dni,

        'accion'          => 'Editar practicante',

        'modulo'          => 'Practicantes',

        'descripcion'     =>
            'Se modificó el practicante: '
            . $practicante->nombre_completo,

        'ip_equipo'       => request()->ip(),

        'user_agent'      => request()->userAgent(),

        'metodo'          => request()->method(),

        'ruta'            => request()->path(),

        'referencia_id'   => $practicante->id,

        'referencia_tipo' => 'practicante',

        'valores_anteriores' =>
            $valoresAnteriores,

        'valores_nuevos' =>
            $practicante->fresh()->toArray(),

        'estado'          => 'Exitoso',
    ]);

    return redirect()
        ->route('practicantes.index')
        ->with(
            'success',
            'Practicante actualizado correctamente.'
        );
}


public function imprimirPorInstitucion($institucion)
{
    $institucion = urldecode($institucion);
    $practicantes = Practicante::with('departamento')
        ->where('institucion', $institucion)
        ->orderBy('nombre_completo')
        ->get();

    $titulo = 'PRACTICANTES - ' . strtoupper($institucion);

    $pdf = Pdf::loadView(
        'practicantes.listado',
        compact(
            'practicantes',
            'titulo'
        )
    );

    return $pdf->stream(
        'practicantes_' .
        Str::slug($institucion) .
        '.pdf'
    );
}

public function imprimirPorAnio($anio)
{
    $practicantes = Practicante::with('departamento')
        ->whereYear('fecha_inicio', $anio)
        ->orderBy('nombre_completo')
        ->get();

    $titulo = 'PRACTICANTES DEL AÑO ' . $anio;

    $pdf = Pdf::loadView(
        'practicantes.listado',
        compact(
            'practicantes',
            'titulo'
        )
    );

    return $pdf->stream(
        'practicantes_' . $anio . '.pdf'
    );
}
}