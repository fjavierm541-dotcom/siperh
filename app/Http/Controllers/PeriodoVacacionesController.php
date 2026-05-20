<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\PeriodoVacacionesSistema;
use App\Models\MovimientoPermisoSistema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\BitacoraHelper;

class PeriodoVacacionesController extends Controller
{
    public function create()
    {
        $empleados = Empleado::all();
        return view('periodos.create', compact('empleados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni_empleado' => 'required|exists:empleados,DNI',
            'anio_laboral.*' => 'required|integer|min:1900',
            'dias_otorgados.*' => 'required|integer|min:0',
            'dias_usados.*' => 'nullable|integer|min:0',
        ]);

        try {

            $empleado = Empleado::where('DNI', $request->dni_empleado)->first();

            if (!$empleado || !$empleado->fecha_nombramiento) {
                throw new \Exception("El empleado no tiene fecha de nombramiento registrada.");
            }

            // CARGA ÚNICA
            $yaTieneHistorial = PeriodoVacacionesSistema::where('dni_empleado', $request->dni_empleado)->exists();

           $anios = $request->anio_laboral ?? [];
           $anios = array_unique($request->anio_laboral ?? []);
            sort($anios);

            $huecos = [];

            for ($j = 0; $j < count($anios) - 1; $j++) {
                if ($anios[$j + 1] != $anios[$j] + 1) {
                    $huecos[] = $anios[$j] + 1;
                }
            }

            if (!empty($huecos)) {
                return back()->withInput()->with('error', 'Faltan años en el historial: ' . implode(', ', $huecos));
            }

                        if ($yaTieneHistorial) {
                            throw new \Exception("Este empleado ya tiene historial registrado.");
                        }

                        DB::transaction(function () use ($request, $empleado) {

                            for ($i = 0; $i < count($request->anio_laboral); $i++) {

                                $anioLaboral = $request->anio_laboral[$i];
                                $anioActual = now()->year;
            $anioIngreso = \Carbon\Carbon::parse($empleado->fecha_nombramiento)->year;

            // No años futuros
            if ($anioLaboral > $anioActual) {
                throw new \Exception("No puedes registrar años futuros.");
            }

            // No antes del ingreso
            if ($anioLaboral < $anioIngreso) {
                throw new \Exception("El año laboral no puede ser menor al año de ingreso del empleado.");
            }
                    $otorgados = $request->dias_otorgados[$i];
                    $usados = $request->dias_usados[$i] ?? 0;

                    if ($usados > $otorgados) {
                        throw new \Exception("Los días usados no pueden ser mayores que los otorgados.");
                    }

                    // BLOQUEAR DUPLICADOS
                    $existe = PeriodoVacacionesSistema::where('dni_empleado', $request->dni_empleado)
                        ->where('anio_laboral', $anioLaboral)
                        ->exists();

                    if ($existe) {
                        throw new \Exception("El año laboral {$anioLaboral} ya está registrado para este empleado.");
                    }

                    // FECHA INICIO (ANIVERSARIO)
                    $fechaInicio = Carbon::parse($empleado->fecha_nombramiento)
                        ->setYear($anioLaboral);

                    // VENCIMIENTO (3 AÑOS)
                    $fechaVencimiento = $fechaInicio->copy()->addYears(3);

                    // ESTADO AUTOMÁTICO (MEJOR UX)
                    $estado = $fechaVencimiento->isPast() ? 'vencido' : 'activo';

                    $periodo = PeriodoVacacionesSistema::create([
                        'dni_empleado' => $request->dni_empleado,
                        'anio_laboral' => $anioLaboral,
                        'dias_otorgados' => $otorgados,
                        'dias_usados' => $usados,
                        'fecha_inicio_periodo' => $fechaInicio,
                        'fecha_vencimiento' => $fechaVencimiento,
                        'estado' => $estado
                    ]);

                    $diasDisponibles = $otorgados - $usados;

                    MovimientoPermisoSistema::create([
                        'dni_empleado' => $request->dni_empleado,
                        'periodo_id' => $periodo->id,
                        'permiso_id' => null,
                        'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                        'categoria' => 'vacaciones',
                        'tipo_movimiento' => 'carga_inicial',
                        'dias_afectados' => $diasDisponibles,
                        'horas_afectadas' => 0,
                        'descripcion' => 'Carga inicial de vacaciones del año laboral ' . $anioLaboral .
                            ': ' . $otorgados . ' días otorgados, ' .
                            $usados . ' días usados, ' .
                            $diasDisponibles . ' días disponibles.',
                    ]);

                    BitacoraHelper::registrar(
                        'crear_periodo_vacaciones',
                        'vacaciones',

                        'Se registró un período de vacaciones del año laboral ' .
                        $anioLaboral .
                        ' para el empleado ' .
                        $empleado->primer_nombre . ' ' .
                        $empleado->primer_apellido,

                        $periodo->id,

                        'periodo_vacaciones',

                        null,

                        [
                            'dni_empleado' => $periodo->dni_empleado,
                            'anio_laboral' => $periodo->anio_laboral,
                            'dias_otorgados' => $periodo->dias_otorgados,
                            'dias_usados' => $periodo->dias_usados,
                            'estado' => $periodo->estado,
                        ]
                    );


                }

            });

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('periodos.create')
            ->with('success', 'Historial de vacaciones registrado correctamente.');
    }

    //reactivar periodos
public function reactivar(Request $request)
{
    $request->validate([
        'periodo_id' => 'required|exists:periodos_vacaciones_sistema,id',
        'motivo' => 'required|string|max:500',
        'documento' => 'nullable|file|max:2048'
    ]);

    try {

        $periodo = PeriodoVacacionesSistema::findOrFail($request->periodo_id);

        if ($periodo->estado !== 'vencido') {
            throw new \Exception("Solo se pueden reactivar períodos vencidos.");
        }

        // FECHA NUEVA
        $fechaNombramiento = Carbon::parse($periodo->empleado->fecha_nombramiento);
        $hoy = now();
        // construir fecha aniversario este año
        $fechaAniversario = $fechaNombramiento->copy()->setYear($hoy->year);
        // si ya pasó → usar siguiente año
        if ($hoy->greaterThan($fechaAniversario)) {
            $fechaAniversario->addYear();
        }
        $nuevaFecha = $fechaAniversario;


        
        // GUARDAR DOCUMENTO PRIMERO
        $path = null;

        if ($request->hasFile('documento')) {
            $path = $request->file('documento')->store('reactivaciones', 'public');
        }

        $datosAnteriores = $periodo->toArray();

        // ACTUALIZAR
        $periodo->update([
            'estado' => 'extendido',
            'extension_hasta' => $nuevaFecha,
            'motivo_extension' => $request->motivo,
            'documento_extension' => $path
        ]);

        BitacoraHelper::registrar(
            'reactivar_periodo_vacaciones',
            'vacaciones',

            'Se reactivó/extiendió un período de vacaciones vencido.',

            $periodo->id,

            'periodo_vacaciones',

            $datosAnteriores,

            $periodo->fresh()->toArray()
        );

    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }

    return back()->with('success', 'Período reactivado correctamente.');
}

}