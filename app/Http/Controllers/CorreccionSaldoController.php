<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Empleado;
use App\Models\PeriodoVacacionesSistema;
use App\Models\MovimientoPermisoSistema;
use App\Models\Compensatorio;
use App\Models\HoraAcumuladaSistema;
use App\Helpers\BitacoraHelper;
use Carbon\Carbon;
use App\Helpers\NotificacionHelper;
use App\Models\User;

class CorreccionSaldoController extends Controller
{
    public function create()
    {
        $empleados = Empleado::orderBy('primer_nombre')
            ->orderBy('primer_apellido')
            ->get();

        $periodos = PeriodoVacacionesSistema::with('empleado')
            ->orderByDesc('anio_laboral')
            ->get();

        return view('ajustes.correccion-saldos', compact('empleados', 'periodos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dni_empleado' => 'required|exists:empleados,DNI',
            'tipo_saldo' => 'required|in:vacaciones,compensatorios,horas',
            'periodo_id' => 'required_if:tipo_saldo,vacaciones|nullable|exists:periodos_vacaciones_sistema,id',
            'operacion' => 'required|in:sumar,restar',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:500',
        ], [
            'dni_empleado.required' => 'Debe seleccionar un empleado.',
            'tipo_saldo.required' => 'Debe seleccionar el tipo de saldo.',
            'periodo_id.required_if' => 'Debe seleccionar un período de vacaciones.',
            'operacion.required' => 'Debe seleccionar si desea sumar o restar.',
            'cantidad.required' => 'Debe ingresar la cantidad.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'motivo.required' => 'Debe ingresar el motivo de la corrección.',
            'motivo.max' => 'El motivo no puede exceder 500 caracteres.',
        ]);

        try {

            DB::transaction(function () use ($request) {

                if ($request->tipo_saldo === 'vacaciones') {
                    $this->ajustarVacaciones($request);
                }

                if ($request->tipo_saldo === 'compensatorios') {
                    $this->ajustarCompensatorios($request);
                }

                if ($request->tipo_saldo === 'horas') {
                    $this->ajustarHoras($request);
                }

            });

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('correcciones-saldos.create')
            ->with('success', 'Corrección de saldo registrada correctamente.');
    }

    private function ajustarVacaciones(Request $request)
    {
        $periodo = PeriodoVacacionesSistema::where('id', $request->periodo_id)
            ->where('dni_empleado', $request->dni_empleado)
            ->firstOrFail();

        $saldoAnterior = max(0, $periodo->dias_otorgados - $periodo->dias_usados);

        $cantidad = (int) $request->cantidad;

        if ($request->operacion === 'restar') {

            if ($cantidad > $saldoAnterior) {
                throw new \Exception('No puede restar más días de los disponibles en este período.');
            }

            $periodo->dias_usados += $cantidad;

            $ajusteTexto = '-' . $cantidad;
            $diasMovimiento = -$cantidad;
        }
//parea permitir permtir sumar dias o restar
        if ($request->operacion === 'sumar') {

        $periodo->dias_otorgados += $cantidad;

        $ajusteTexto = '+' . $cantidad;
        $diasMovimiento = $cantidad;
    }

        $periodo->save();

        $saldoNuevo = max(0, $periodo->dias_otorgados - $periodo->dias_usados);

        MovimientoPermisoSistema::create([
            'dni_empleado' => $request->dni_empleado,
            'periodo_id' => $periodo->id,
            'permiso_id' => null,
            'usuario_nombre' => auth()->user()->name ?? 'Sistema',
            'categoria' => 'vacaciones',
            'tipo_movimiento' => 'ajuste_manual',
            'dias_afectados' => $diasMovimiento,
            'horas_afectadas' => 0,
            'descripcion' => 'Corrección de saldo de vacaciones. Año laboral: ' .
                $periodo->anio_laboral .
                '. Saldo anterior: ' . $saldoAnterior .
                ' día(s). Ajuste: ' . $ajusteTexto .
                ' día(s). Saldo nuevo en el período modificado: ' . $saldoNuevo .
                ' día(s). Motivo: ' . $request->motivo,
        ]);
    }

    private function ajustarCompensatorios(Request $request)
    {
        $cantidad = (int) $request->cantidad;

        $saldoAnterior = Compensatorio::where('dni_empleado', $request->dni_empleado)
            ->where('estado', 'activo')
            ->sum('dias_disponibles');

        if ($request->operacion === 'restar') {

            if ($cantidad > $saldoAnterior) {
                throw new \Exception('No puede restar más días compensatorios de los disponibles.');
            }

            $pendiente = $cantidad;

            $bolsas = Compensatorio::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->where('dias_disponibles', '>', 0)
                ->orderBy('fecha_vencimiento')
                ->orderBy('fecha_origen')
                ->get();

            foreach ($bolsas as $bolsa) {

                if ($pendiente <= 0) {
                    break;
                }

                $usar = min($bolsa->dias_disponibles, $pendiente);

                $bolsa->dias_disponibles -= $usar;

                if ($bolsa->dias_disponibles <= 0) {
                    $bolsa->estado = 'agotado';
                }

                $bolsa->save();

                $pendiente -= $usar;
            }

            $saldoNuevo = Compensatorio::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->sum('dias_disponibles');

            MovimientoPermisoSistema::create([
                'dni_empleado' => $request->dni_empleado,
                'periodo_id' => null,
                'permiso_id' => null,
                'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                'categoria' => 'compensatorio',
                'tipo_movimiento' => 'ajuste_manual',
                'dias_afectados' => -$cantidad,
                'horas_afectadas' => 0,
                'descripcion' => 'Corrección de saldo compensatorio. Saldo anterior: ' .
                    $saldoAnterior .
                    ' día(s). Ajuste: -' . $cantidad .
                    ' día(s). Saldo nuevo: ' . $saldoNuevo .
                    ' día(s). Motivo: ' . $request->motivo,
            ]);

            return;
        }

        if ($request->operacion === 'sumar') {

            $fechaOrigen = now()->toDateString();
            $fechaVencimiento = now()->addYear()->toDateString();

            Compensatorio::create([
                'dni_empleado' => $request->dni_empleado,
                'dias_otorgados' => $cantidad,
                'dias_disponibles' => $cantidad,
                'fecha_origen' => $fechaOrigen,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => 'activo',
                'origen' => 'ajuste_manual',
                'referencia_id' => 0,
            ]);

            $saldoNuevo = Compensatorio::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->sum('dias_disponibles');

            MovimientoPermisoSistema::create([
                'dni_empleado' => $request->dni_empleado,
                'periodo_id' => null,
                'permiso_id' => null,
                'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                'categoria' => 'compensatorio',
                'tipo_movimiento' => 'ajuste_manual',
                'dias_afectados' => $cantidad,
                'horas_afectadas' => 0,
                'descripcion' => 'Corrección de saldo compensatorio. Saldo anterior: ' .
                    $saldoAnterior .
                    ' día(s). Ajuste: +' . $cantidad .
                    ' día(s). Saldo nuevo: ' . $saldoNuevo .
                    ' día(s). Motivo: ' . $request->motivo,
            ]);
        }
    }

    private function ajustarHoras(Request $request)
    {
        $cantidad = (float) $request->cantidad;

        $saldoAnterior = HoraAcumuladaSistema::where('dni_empleado', $request->dni_empleado)
            ->where('estado', 'activo')
            ->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
            ->value('total') ?? 0;

        if ($request->operacion === 'restar') {

            if ($cantidad > $saldoAnterior) {
                throw new \Exception('No puede restar más horas de las disponibles.');
            }

            $pendiente = $cantidad;

            $bolsas = HoraAcumuladaSistema::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->whereRaw('(horas_otorgadas - horas_usadas) > 0')
                ->orderBy('fecha_vencimiento')
                ->orderBy('fecha_origen')
                ->get();

            foreach ($bolsas as $bolsa) {

                if ($pendiente <= 0) {
                    break;
                }

                $disponibles = $bolsa->horas_otorgadas - $bolsa->horas_usadas;

                $usar = min($disponibles, $pendiente);

                $bolsa->horas_usadas += $usar;

                if ($bolsa->horas_usadas >= $bolsa->horas_otorgadas) {
                    $bolsa->estado = 'agotado';
                }

                $bolsa->save();

                $pendiente -= $usar;
            }

            $saldoNuevo = HoraAcumuladaSistema::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
                ->value('total') ?? 0;

            MovimientoPermisoSistema::create([
                'dni_empleado' => $request->dni_empleado,
                'periodo_id' => null,
                'permiso_id' => null,
                'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                'categoria' => 'horas',
                'tipo_movimiento' => 'ajuste_manual',
                'dias_afectados' => 0,
                'horas_afectadas' => -$cantidad,
                'descripcion' => 'Corrección de saldo de horas acumuladas. Saldo anterior: ' .
                    $saldoAnterior .
                    ' hora(s). Ajuste: -' . $cantidad .
                    ' hora(s). Saldo nuevo: ' . $saldoNuevo .
                    ' hora(s). Motivo: ' . $request->motivo,
            ]);

            return;
        }

        if ($request->operacion === 'sumar') {

            HoraAcumuladaSistema::create([
                'dni_empleado' => $request->dni_empleado,
                'horas_otorgadas' => $cantidad,
                'horas_usadas' => 0,
                'fecha_origen' => now()->toDateString(),
                'fecha_vencimiento' => now()->addYear()->toDateString(),
                'estado' => 'activo',
                'origen' => 'ajuste_manual',
                'referencia_id' => null,
            ]);

            $saldoNuevo = HoraAcumuladaSistema::where('dni_empleado', $request->dni_empleado)
                ->where('estado', 'activo')
                ->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
                ->value('total') ?? 0;

            MovimientoPermisoSistema::create([
                'dni_empleado' => $request->dni_empleado,
                'periodo_id' => null,
                'permiso_id' => null,
                'usuario_nombre' => auth()->user()->name ?? 'Sistema',
                'categoria' => 'horas',
                'tipo_movimiento' => 'ajuste_manual',
                'dias_afectados' => 0,
                'horas_afectadas' => $cantidad,
                'descripcion' => 'Corrección de saldo de horas acumuladas. Saldo anterior: ' .
                    $saldoAnterior .
                    ' hora(s). Ajuste: +' . $cantidad .
                    ' hora(s). Saldo nuevo: ' . $saldoNuevo .
                    ' hora(s). Motivo: ' . $request->motivo,
            ]);
        }
    }



    public function createPeriodoFaltante()
{
    $empleados = Empleado::orderBy('primer_nombre')
        ->orderBy('primer_apellido')
        ->get();

    return view('ajustes.periodo-faltante', compact('empleados'));
}


public function storePeriodoFaltante(Request $request)
{
    $request->validate([
        'dni_empleado' => 'required|exists:empleados,DNI',
        'anio_laboral' => 'required|integer|min:1900',
        'dias_otorgados' => 'required|integer|min:1',
        'motivo' => 'required|string|max:500',
    ]);

    try {

        $empleado = Empleado::where('DNI', $request->dni_empleado)
            ->firstOrFail();

        if (!$empleado->fecha_nombramiento) {
            throw new \Exception(
                'El empleado no tiene fecha de nombramiento.'
            );
        }

        $anioActual = now()->year;

        $anioIngreso = Carbon::parse(
            $empleado->fecha_nombramiento
        )->year;

        // No antes del ingreso
        if ($request->anio_laboral < $anioIngreso) {

            throw new \Exception(
                'El año laboral no puede ser menor al año de ingreso.'
            );
        }

        // No futuro
        if ($request->anio_laboral > $anioActual) {

            throw new \Exception(
                'No puede registrar períodos futuros.'
            );
        }

        // Evitar duplicados
        $existe = PeriodoVacacionesSistema::where(
                'dni_empleado',
                $request->dni_empleado
            )
            ->where(
                'anio_laboral',
                $request->anio_laboral
            )
            ->exists();

        if ($existe) {

            throw new \Exception(
                'Este período ya existe para el empleado.'
            );
        }

        // Fecha aniversario
        $fechaInicio = Carbon::parse(
            $empleado->fecha_nombramiento
        )->setYear($request->anio_laboral);

        // Vence 3 años después
        $fechaVencimiento = $fechaInicio
            ->copy()
            ->addYears(3);

        $estado = $fechaVencimiento->isPast()
            ? 'vencido'
            : 'activo';

        DB::transaction(function () use (
            $request,
            $empleado,
            $fechaInicio,
            $fechaVencimiento,
            $estado
        ) {

            $periodo = PeriodoVacacionesSistema::create([

                'dni_empleado' => $request->dni_empleado,

                'anio_laboral' => $request->anio_laboral,

                'dias_otorgados' => $request->dias_otorgados,

                'dias_usados' => 0,

                'fecha_inicio_periodo' => $fechaInicio,

                'fecha_vencimiento' => $fechaVencimiento,

                'estado' => $estado,

                'tipo_periodo' => 'correccion_manual',

            ]);

            MovimientoPermisoSistema::create([

                'dni_empleado' => $request->dni_empleado,

                'periodo_id' => $periodo->id,

                'permiso_id' => null,

                'usuario_nombre' => auth()->user()->name ?? 'Sistema',

                'categoria' => 'vacaciones',

                'tipo_movimiento' => 'periodo_faltante',

                'dias_afectados' => $request->dias_otorgados,

                'horas_afectadas' => 0,

                'descripcion' =>
                    'Se agregó manualmente el período laboral ' .
                    $request->anio_laboral .
                    ' con ' .
                    $request->dias_otorgados .
                    ' día(s). Motivo: ' .
                    $request->motivo,
            ]);

            BitacoraHelper::registrar(

                'crear_periodo_faltante',

                'vacaciones',

                'Se agregó manualmente el período laboral ' .
                $request->anio_laboral .
                ' para el empleado ' .
                $empleado->primer_nombre .
                ' ' .
                $empleado->primer_apellido,

                $periodo->id,

                'periodo_vacaciones',

                null,

                [
                    'anio_laboral' => $periodo->anio_laboral,
                    'dias_otorgados' => $periodo->dias_otorgados,
                    'estado' => $periodo->estado,
                    'tipo_periodo' => $periodo->tipo_periodo,
                ]
            );

            // Notificación superadmin
            NotificacionHelper::crear(

                null,

                'superadmin',

                'Período faltante agregado',

                'Se agregó manualmente el período laboral ' .
                $request->anio_laboral .
                ' al empleado ' .
                $empleado->primer_nombre .
                ' ' .
                $empleado->primer_apellido . '.',

                'warning',

                'vacaciones',

                route('correcciones-saldos.create')
            );

        });

    } catch (\Exception $e) {

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }

    return redirect()
        ->route('correcciones-saldos.create')
        ->with(
            'success',
            'Período faltante agregado correctamente.'
        );
}
}