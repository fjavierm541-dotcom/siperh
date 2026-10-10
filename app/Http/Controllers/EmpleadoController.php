<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use App\Models\DiasAcumuladosSistema;
use App\Models\PeriodoVacacionesSistema;
use Carbon\Carbon;
use App\Models\MovimientoPermisoSistema;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DepartamentoMuni;
use App\Models\DocumentoEmpleado;
use Illuminate\Support\Facades\DB;
use App\Models\PermisoSistema;
use Illuminate\Validation\Rule;
use App\Helpers\BitacoraHelper;
use setasign\Fpdi\Fpdi;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // 🔄 Marcar vencidos automáticamente
    PeriodoVacacionesSistema::where('estado', 'activo')
        ->where(function ($query) {
            $query->whereNull('extension_hasta')
                  ->whereDate('fecha_vencimiento', '<', Carbon::today());
        })
        ->orWhere(function ($query) {
            $query->whereNotNull('extension_hasta')
                  ->whereDate('extension_hasta', '<', Carbon::today());
        })
        ->update(['estado' => 'vencido']);

        // 👤 Empleados únicos
    $filtroEstadoEmpleado = $request->get('estado_empleado', 'activo');

    $empleadosQuery = Empleado::query();

    if ($filtroEstadoEmpleado === 'activo') {

        $empleadosQuery->where(function ($query) {
            $query->where('estado_empleado', 'activo')
                ->orWhereNull('estado_empleado');
        });

    } elseif ($filtroEstadoEmpleado === 'inactivo') {

        $empleadosQuery->where('estado_empleado', 'inactivo');

    } elseif ($filtroEstadoEmpleado === 'todos') {

        // No aplica filtro. Muestra activos e inactivos.
    }

    $empleados = $empleadosQuery
        ->orderBy('primer_nombre')
        ->get()
        ->unique('DNI')
        ->values();

    foreach ($empleados as $empleado) {

        // ===== VACACIONES DISPONIBLES =====
        $diasVacaciones = PeriodoVacacionesSistema::where('dni_empleado', $empleado->DNI)
            ->whereIn('estado', ['activo', 'extendido'])
            ->selectRaw('SUM(dias_otorgados - dias_usados) as total')
            ->value('total') ?? 0;

        // ===== COMPENSATORIOS DISPONIBLES =====
        $diasCompensatorios = DB::table('compensatorios_sistema')
            ->where('dni_empleado', $empleado->DNI)
            ->where('estado', 'activo')
            ->sum('dias_disponibles');

        // ===== HORAS DISPONIBLES =====
        $horasDisponibles = DB::table('horas_acumuladas_sistema')
            ->where('dni_empleado', $empleado->DNI)
            ->where('estado', 'activo')
            ->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
            ->value('total') ?? 0;

        // Total para mostrar en la vista
        $empleado->dias_disponibles = (int) $diasVacaciones + (int) $diasCompensatorios;
        $empleado->horas_disponibles = (int) $horasDisponibles;

        // ===== SEMÁFORO =====
        $periodoProximo = PeriodoVacacionesSistema::where('dni_empleado', $empleado->DNI)
            ->whereIn('estado', ['activo', 'extendido'])
            ->whereRaw('(dias_otorgados - dias_usados) > 0')
            ->orderByRaw('COALESCE(extension_hasta, fecha_vencimiento) asc')
            ->first();

        if ($periodoProximo) {

            $inicioPeriodo = Carbon::parse($periodoProximo->fecha_inicio_periodo);
            $hoy = Carbon::today();

            $aniosTranscurridos = $inicioPeriodo->diffInYears($hoy);

            if ($aniosTranscurridos < 1) {
                $empleado->semaforo = 'verde';
            } elseif ($aniosTranscurridos < 2) {
                $empleado->semaforo = 'amarillo';
            } else {
                $empleado->semaforo = 'rojo';
            }

        } else {
            $empleado->semaforo = 'verde';
        }
    }

    // 🔍 BUSCADOR
    if ($request->filled('buscar')) {
        $buscar = strtolower($request->buscar);

        $empleados = $empleados->filter(function ($empleado) use ($buscar) {
            $nombreCompleto = strtolower(
                ($empleado->primer_nombre ?? '') . ' ' .
                ($empleado->segundo_nombre ?? '') . ' ' .
                ($empleado->primer_apellido ?? '') . ' ' .
                ($empleado->segundo_apellido ?? '')
            );

            return str_contains($nombreCompleto, $buscar)
                || str_contains(strtolower($empleado->DNI), $buscar);
        })->values();
    }

    // 🔍 FILTRO POR SEXO
    if ($request->filled('sexo')) {
        $empleados = $empleados->where('sexo', $request->sexo)->values();
    }

    // 🔍 FILTRO POR SEMÁFORO
    if ($request->filled('estado')) {
        $empleados = $empleados->where('semaforo', $request->estado)->values();
    }

    // 🔢 PAGINACIÓN MANUAL
    $page = $request->get('page', 1);
    $perPage = 15;

    $empleados = new \Illuminate\Pagination\LengthAwarePaginator(
        $empleados->forPage($page, $perPage)->values(),
        $empleados->count(),
        $perPage,
        $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return view('empleados.index', compact('empleados'));
}







public function generarVacaciones()
{
    $hoy = Carbon::today();

    $empleados = Empleado::all();

    foreach ($empleados as $empleado) {

        if (!$empleado->fecha_nombramiento) continue;

        $fechaIngreso = Carbon::parse($empleado->fecha_nombramiento);

        // ¿Hoy cumple aniversario?
        if ($fechaIngreso->month != $hoy->month ||
            $fechaIngreso->day != $hoy->day) {
            continue;
        }

        $aniosCumplidos = $fechaIngreso->diffInYears($hoy);

        if ($aniosCumplidos < 1) continue;

        // Tabla legal
        if ($aniosCumplidos == 1) $dias = 12;
        elseif ($aniosCumplidos == 2) $dias = 15;
        elseif ($aniosCumplidos == 3) $dias = 18;
        elseif ($aniosCumplidos == 4) $dias = 22;
        elseif ($aniosCumplidos == 5) $dias = 26;
        else $dias = 30;

        // Evitar duplicados
        $existe = PeriodoVacacionesSistema::where('dni_empleado', $empleado->DNI)
            ->where('anio_laboral', $aniosCumplidos)
            ->exists();

        if ($existe) continue;

        PeriodoVacacionesSistema::create([
            'dni_empleado' => $empleado->DNI,
            'anio_laboral' => $aniosCumplidos,
            'dias_otorgados' => $dias,
            'dias_usados' => 0,
            'fecha_inicio_periodo' => $hoy,
            'fecha_vencimiento' => $hoy->copy()->addYears(2),
            'estado' => 'activo'
        ]);
    }

    return back()->with('success', 'Proceso de generación ejecutado correctamente.');
}





    /**
     *CREAR EMPLEADOS
     
   */

public function create()
{

$departamentos = DepartamentoMuni::orderBy('codigo')->get();

return view('empleados.create', compact('departamentos'));

}






public function store(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Preparar beneficiarios opcionales
    |--------------------------------------------------------------------------
    */

    for ($i = 1; $i <= 7; $i++) {
        $request->merge([
            "nombre_beneficiario$i" =>
                $request->input("nombre_beneficiario$i") ?: 'Vacío',

            "porcentaje_beneficiario$i" =>
                $request->input("porcentaje_beneficiario$i") ?: 0,

            "parentezco_beneficiario$i" =>
                $request->input("parentezco_beneficiario$i") ?: 'Vacío',

            "DNI_beneficiario$i" =>
                $request->input("DNI_beneficiario$i") ?: '0000-0000-00000',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validar total de porcentajes de beneficiarios
    |--------------------------------------------------------------------------
    */

    $totalPorcentaje = 0;
    $hayBeneficiarios = false;

    for ($i = 1; $i <= 7; $i++) {

        $nombre = trim(
            (string) $request->input("nombre_beneficiario$i")
        );

        $porcentaje = (int) $request->input(
            "porcentaje_beneficiario$i",
            0
        );

        if ($nombre !== '' && $nombre !== 'Vacío') {
            $hayBeneficiarios = true;
            $totalPorcentaje += $porcentaje;
        }
    }

    if ($hayBeneficiarios) {

        if ($totalPorcentaje < 100) {

            $faltante = 100 - $totalPorcentaje;

            return back()
                ->withErrors([
                    'beneficiarios' =>
                        "Los porcentajes de beneficiarios suman {$totalPorcentaje}%. "
                        . "Falta asignar {$faltante}% para completar el 100%."
                ])
                ->withInput();
        }

        if ($totalPorcentaje > 100) {

            $excedente = $totalPorcentaje - 100;

            return back()
                ->withErrors([
                    'beneficiarios' =>
                        "Los porcentajes de beneficiarios suman {$totalPorcentaje}%. "
                        . "Excede el límite por {$excedente}%."
                ])
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Contactos de emergencia
    |--------------------------------------------------------------------------
    |
    | Se conserva este comportamiento porque el sistema ya está en producción
    | y puede haber columnas que actualmente no acepten NULL.
    |
    */

    $request->merge([
        'nombre_contacto1' =>
            $request->input('nombre_contacto1') ?: 'Ninguno',

        'telefono_contacto1' =>
            $request->input('telefono_contacto1') ?: '99999999',

        'parentezco_contacto1' =>
            $request->input('parentezco_contacto1') ?: 'Otro',

        'nombre_contacto2' =>
            $request->input('nombre_contacto2') ?: 'Ninguno',

        'telefono_contacto2' =>
            $request->input('telefono_contacto2') ?: '99999999',

        'parentezco_contacto2' =>
            $request->input('parentezco_contacto2') ?: 'Otro',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */

    $request->validate([

        // =========================
        // DATOS GENERALES
        // =========================

        'codigo' => [
            'required',
            'regex:/^[0-9]{1,4}$/'
        ],

        'DNI' => [
            'required',
            'unique:empleados,DNI',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],

        'RTN' => [
            'required',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{6}$/'
        ],

        'primer_nombre' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'segundo_nombre' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'primer_apellido' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'segundo_apellido' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'sexo' => [
            'required',
            'in:Masculino,Femenino'
        ],

        'estado_civil' => [
            'required',
            'in:Soltero(a),Casado(a),Unión Libre,Divorciado(a),Viudo(a)'
        ],

        'nacionalidad' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'tipo_sangre' => [
            'required',
            'in:A+,A-,B+,B-,AB+,AB-,O+,O-'
        ],


        // =========================
        // FECHA DE NACIMIENTO
        // =========================

        'dia_nacimiento' => [
            'required',
            'integer',
            'min:1',
            'max:31'
        ],

        'mes_nacimiento' => [
            'required',
            'integer',
            'min:1',
            'max:12'
        ],

        'anio_nacimiento' => [
            'required',
            'integer',
            'min:1940',
            'max:' . (date('Y') - 18)
        ],


        // =========================
        // INFORMACIÓN DE CONTACTO
        // =========================

        'telefono_celular' => [
            'required',
            'regex:/^(3|8|9)[0-9]{7}$/'
        ],

        'telefono_fijo' => [
            'nullable',
            'regex:/^2[0-9]{7}$/'
        ],

        'direccion_domicilio' => [
            'required',
            'string',
            'max:255'
        ],

        'referencia_domicilio' => [
            'required',
            'string',
            'max:255'
        ],

        'nivel_educativo' => [
            'required',
            'in:Nivel Primario,Nivel Secundario,Nivel Superior,Postgrado'
        ],


        // =========================
        // CONTACTOS DE EMERGENCIA
        // =========================

        'nombre_contacto1' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'telefono_contacto1' => [
            'required',
            'regex:/^[0-9]{8}$/'
        ],

        'parentezco_contacto1' => [
            'required',
            'in:Padre,Madre,Hermano(a),Abuelo(a),Tío(a),Primo(a),Esposo(a),Pareja,Hijo(a),Amigo(a),Vecino(a),Otro'
        ],

        'nombre_contacto2' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'telefono_contacto2' => [
            'required',
            'regex:/^[0-9]{8}$/'
        ],

        'parentezco_contacto2' => [
            'required',
            'in:Padre,Madre,Hermano(a),Abuelo(a),Tío(a),Primo(a),Esposo(a),Pareja,Hijo(a),Amigo(a),Vecino(a),Otro'
        ],


        // =========================
        // BENEFICIARIOS
        // =========================

        'nombre_beneficiario1' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario1' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario1' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario1' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario2' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario2' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario2' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario2' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario3' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario3' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario3' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario3' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario4' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario4' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario4' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario4' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario5' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario5' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario5' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario5' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario6' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario6' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario6' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario6' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        'nombre_beneficiario7' => [
            'nullable',
            'string',
            'max:100'
        ],

        'porcentaje_beneficiario7' => [
            'nullable',
            'numeric',
            'min:0',
            'max:100'
        ],

        'parentezco_beneficiario7' => [
            'nullable',
            'string',
            'max:50'
        ],

        'DNI_beneficiario7' => [
            'nullable',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/'
        ],


        // =========================
        // INFORMACIÓN LABORAL
        // =========================

        'puesto' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'min:3',
            'max:100'
        ],

        'puesto_funcional' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'min:3',
            'max:100'
        ],

        'fecha_nombramiento' => [
            'required',
            'date',
            'before_or_equal:today'
        ],

        'tipo' => [
            'required',
            'in:Acuerdo,Contrato'
        ],

        'fecha_fin_contrato' => [
            'nullable',
            'date',
            'after_or_equal:fecha_nombramiento',
            'required_if:tipo,Contrato'
        ],

        'salario_inicial' => [
            'required',
            'regex:/^L\.?\s?[0-9]{1,3}(,[0-9]{3})*(\.[0-9]{2})?$/'
        ],

        'departamento_id' => [
            'required',
            'exists:departamentos_muni,id'
        ],


        // =========================
        // DOCUMENTOS
        // =========================

        'copia_dni' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'copia_rtn' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'acuerdo' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'nota_traslado' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

    ], [

        // =========================
        // CÓDIGO
        // =========================

        'codigo.required' =>
            'El código es obligatorio.',

        'codigo.regex' =>
            'El código debe contener entre 1 y 4 dígitos.',


        // =========================
        // DNI / RTN
        // =========================

        'DNI.required' =>
            'El DNI es obligatorio.',

        'DNI.unique' =>
            'Este DNI ya está registrado.',

        'DNI.regex' =>
            'El DNI debe tener el formato 0000-0000-00000.',

        'RTN.required' =>
            'El RTN es obligatorio.',

        'RTN.regex' =>
            'El RTN debe tener el formato 0000-0000-000000.',


        // =========================
        // NOMBRES Y APELLIDOS
        // =========================

        'primer_nombre.required' =>
            'El primer nombre es obligatorio.',

        'primer_nombre.regex' =>
            'El primer nombre solo puede contener letras y espacios.',

        'primer_nombre.max' =>
            'El primer nombre no puede superar los 50 caracteres.',

        'segundo_nombre.regex' =>
            'El segundo nombre solo puede contener letras y espacios.',

        'segundo_nombre.max' =>
            'El segundo nombre no puede superar los 50 caracteres.',

        'primer_apellido.required' =>
            'El primer apellido es obligatorio.',

        'primer_apellido.regex' =>
            'El primer apellido solo puede contener letras y espacios.',

        'primer_apellido.max' =>
            'El primer apellido no puede superar los 50 caracteres.',

        'segundo_apellido.regex' =>
            'El segundo apellido solo puede contener letras y espacios.',

        'segundo_apellido.max' =>
            'El segundo apellido no puede superar los 50 caracteres.',


        // =========================
        // FECHA DE NACIMIENTO
        // =========================

        'dia_nacimiento.required' =>
            'Debe seleccionar el día de nacimiento.',

        'dia_nacimiento.integer' =>
            'El día de nacimiento no es válido.',

        'dia_nacimiento.min' =>
            'El día de nacimiento no es válido.',

        'dia_nacimiento.max' =>
            'El día de nacimiento no es válido.',

        'mes_nacimiento.required' =>
            'Debe seleccionar el mes de nacimiento.',

        'mes_nacimiento.integer' =>
            'El mes de nacimiento no es válido.',

        'mes_nacimiento.min' =>
            'El mes de nacimiento no es válido.',

        'mes_nacimiento.max' =>
            'El mes de nacimiento no es válido.',

        'anio_nacimiento.required' =>
            'Debe seleccionar el año de nacimiento.',

        'anio_nacimiento.integer' =>
            'El año de nacimiento no es válido.',

        'anio_nacimiento.min' =>
            'El año de nacimiento no es válido.',

        'anio_nacimiento.max' =>
            'El empleado debe tener al menos 18 años.',


        // =========================
        // OTROS DATOS GENERALES
        // =========================

        'sexo.required' =>
            'Debe seleccionar el sexo.',

        'sexo.in' =>
            'El sexo seleccionado no es válido.',

        'estado_civil.required' =>
            'Debe seleccionar el estado civil.',

        'estado_civil.in' =>
            'El estado civil seleccionado no es válido.',

        'nacionalidad.required' =>
            'La nacionalidad es obligatoria.',

        'nacionalidad.regex' =>
            'La nacionalidad solo puede contener letras y espacios.',

        'nacionalidad.max' =>
            'La nacionalidad no puede superar los 50 caracteres.',

        'tipo_sangre.required' =>
            'Debe seleccionar el tipo de sangre.',

        'tipo_sangre.in' =>
            'El tipo de sangre seleccionado no es válido.',


        // =========================
        // CONTACTO
        // =========================

        'telefono_celular.required' =>
            'El teléfono celular es obligatorio.',

        'telefono_celular.regex' =>
            'El celular debe iniciar con 3, 8 o 9 y contener exactamente 8 dígitos.',

        'telefono_fijo.regex' =>
            'El teléfono fijo debe iniciar con 2 y contener exactamente 8 dígitos.',

        'direccion_domicilio.required' =>
            'La dirección del domicilio es obligatoria.',

        'direccion_domicilio.max' =>
            'La dirección del domicilio no puede superar los 255 caracteres.',

        'referencia_domicilio.required' =>
            'La referencia del domicilio es obligatoria.',

        'referencia_domicilio.max' =>
            'La referencia del domicilio no puede superar los 255 caracteres.',

        'nivel_educativo.required' =>
            'Debe seleccionar el nivel educativo.',

        'nivel_educativo.in' =>
            'El nivel educativo seleccionado no es válido.',


        // =========================
        // CONTACTOS DE EMERGENCIA
        // =========================

        'nombre_contacto1.required' =>
            'El nombre del primer contacto es obligatorio.',

        'nombre_contacto1.regex' =>
            'El nombre del primer contacto solo puede contener letras y espacios.',

        'nombre_contacto1.max' =>
            'El nombre del primer contacto no puede superar los 50 caracteres.',

        'telefono_contacto1.required' =>
            'El teléfono del primer contacto es obligatorio.',

        'telefono_contacto1.regex' =>
            'El teléfono del primer contacto debe contener exactamente 8 números.',

        'parentezco_contacto1.required' =>
            'Debe seleccionar el parentesco del primer contacto.',

        'parentezco_contacto1.in' =>
            'El parentesco del primer contacto no es válido.',


        'nombre_contacto2.required' =>
            'El nombre del segundo contacto es obligatorio.',

        'nombre_contacto2.regex' =>
            'El nombre del segundo contacto solo puede contener letras y espacios.',

        'nombre_contacto2.max' =>
            'El nombre del segundo contacto no puede superar los 50 caracteres.',

        'telefono_contacto2.required' =>
            'El teléfono del segundo contacto es obligatorio.',

        'telefono_contacto2.regex' =>
            'El teléfono del segundo contacto debe contener exactamente 8 números.',

        'parentezco_contacto2.required' =>
            'Debe seleccionar el parentesco del segundo contacto.',

        'parentezco_contacto2.in' =>
            'El parentesco del segundo contacto no es válido.',


        // =========================
        // BENEFICIARIOS
        // =========================

        'DNI_beneficiario1.regex' =>
            'El DNI del beneficiario 1 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario2.regex' =>
            'El DNI del beneficiario 2 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario3.regex' =>
            'El DNI del beneficiario 3 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario4.regex' =>
            'El DNI del beneficiario 4 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario5.regex' =>
            'El DNI del beneficiario 5 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario6.regex' =>
            'El DNI del beneficiario 6 debe tener el formato 0000-0000-00000.',

        'DNI_beneficiario7.regex' =>
            'El DNI del beneficiario 7 debe tener el formato 0000-0000-00000.',


        // =========================
        // PUESTO
        // =========================

        'puesto.required' =>
            'El puesto de nombramiento es obligatorio.',

        'puesto.regex' =>
            'El puesto de nombramiento solo puede contener letras y espacios.',

        'puesto.min' =>
            'El puesto de nombramiento debe tener al menos 3 caracteres.',

        'puesto.max' =>
            'El puesto de nombramiento no puede superar los 100 caracteres.',

        'puesto_funcional.regex' =>
            'El puesto funcional solo puede contener letras y espacios.',

        'puesto_funcional.min' =>
            'El puesto funcional debe tener al menos 3 caracteres.',

        'puesto_funcional.max' =>
            'El puesto funcional no puede superar los 100 caracteres.',


        // =========================
        // FECHAS LABORALES
        // =========================

        'fecha_nombramiento.required' =>
            'Debe ingresar la fecha de nombramiento.',

        'fecha_nombramiento.date' =>
            'Debe ingresar una fecha de nombramiento válida.',

        'fecha_nombramiento.before_or_equal' =>
            'La fecha de nombramiento no puede ser futura.',

        'tipo.required' =>
            'Debe seleccionar el tipo de contrato.',

        'tipo.in' =>
            'El tipo de contrato seleccionado no es válido.',

        'fecha_fin_contrato.required_if' =>
            'Debe ingresar la fecha de finalización del contrato.',

        'fecha_fin_contrato.date' =>
            'La fecha de finalización del contrato no es válida.',

        'fecha_fin_contrato.after_or_equal' =>
            'La fecha de finalización del contrato no puede ser anterior a la fecha de nombramiento.',


        // =========================
        // SALARIO
        // =========================

        'salario_inicial.required' =>
            'El salario inicial es obligatorio.',

        'salario_inicial.regex' =>
            'El salario debe tener el formato L. 12,000.00.',


        // =========================
        // DEPARTAMENTO
        // =========================

        'departamento_id.required' =>
            'Debe seleccionar un departamento.',

        'departamento_id.exists' =>
            'El departamento seleccionado no es válido.',


        // =========================
        // DOCUMENTOS
        // =========================

        'copia_dni.mimes' =>
            'La copia del DNI debe ser un archivo PDF, JPG, JPEG o PNG.',

        'copia_dni.max' =>
            'La copia del DNI no debe superar los 5 MB.',

        'copia_rtn.mimes' =>
            'La copia del RTN debe ser un archivo PDF, JPG, JPEG o PNG.',

        'copia_rtn.max' =>
            'La copia del RTN no debe superar los 5 MB.',

        'acuerdo.mimes' =>
            'El acuerdo o contrato debe ser un archivo PDF, JPG, JPEG o PNG.',

        'acuerdo.max' =>
            'El acuerdo o contrato no debe superar los 5 MB.',

        'nota_traslado.mimes' =>
            'La nota de traslado debe ser un archivo PDF, JPG, JPEG o PNG.',

        'nota_traslado.max' =>
            'La nota de traslado no debe superar los 5 MB.',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Comprobar que la combinación día / mes / año sea una fecha real
    |--------------------------------------------------------------------------
    */

    if (!checkdate(
        (int) $request->mes_nacimiento,
        (int) $request->dia_nacimiento,
        (int) $request->anio_nacimiento
    )) {
        return back()
            ->withErrors([
                'dia_nacimiento' =>
                    'La fecha de nacimiento seleccionada no es válida.'
            ])
            ->withInput();
    }


    /*
    |--------------------------------------------------------------------------
    | Preparar salario
    |--------------------------------------------------------------------------
    */

    if ($request->filled('salario_inicial')) {

        $salario = $request->salario_inicial;

        $salario = str_replace('L.', '', $salario);
        $salario = str_replace(' ', '', $salario);
        $salario = str_replace(',', '', $salario);

        $request->merge([
            'salario_inicial' => $salario
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Fecha fin de contrato
    |--------------------------------------------------------------------------
    */

    if ($request->tipo === 'Acuerdo') {
        $request->merge([
            'fecha_fin_contrato' => null
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Estado inicial
    |--------------------------------------------------------------------------
    */

    $request->merge([
        'estado_empleado' => 'activo'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Puesto funcional automático
    |--------------------------------------------------------------------------
    */

    if (empty($request->puesto_funcional)) {
        $request->merge([
            'puesto_funcional' => $request->puesto
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Preparar datos del empleado
    |--------------------------------------------------------------------------
    */

    $data = $request->except([
        '_token',
        'copia_dni',
        'copia_rtn',
        'acuerdo',
        'nota_traslado',
    ]);

    $data['usuario_crea'] =
        auth()->user()->name ?? 'Sistema';


    /*
    |--------------------------------------------------------------------------
    | Crear empleado
    |--------------------------------------------------------------------------
    */

    $empleado = Empleado::create($data);


    /*
    |--------------------------------------------------------------------------
    | Registrar en bitácora
    |--------------------------------------------------------------------------
    */

    BitacoraHelper::registrar(
        'crear_empleado',
        'empleados',

        'Se registró un nuevo empleado: ' .
        $empleado->primer_nombre . ' ' .
        $empleado->primer_apellido,

        $empleado->DNI,

        'empleado',

        null,

        $empleado->toArray()
    );


    /*
    |--------------------------------------------------------------------------
    | Guardar documentos
    |--------------------------------------------------------------------------
    */

    $documentos = [
        'copia_dni' => 'Copia DNI',
        'copia_rtn' => 'Copia RTN',
        'acuerdo' => 'Acuerdo',
        'nota_traslado' => 'Nota Traslado',
    ];

    foreach ($documentos as $campo => $tipoDocumento) {

        if ($request->hasFile($campo)) {

            $ruta = $request->file($campo)
                ->store(
                    "empleados/{$empleado->DNI}/documentos",
                    'public'
                );

            DocumentoEmpleado::create([
                'dni_empleado' => $empleado->DNI,
                'tipo_documento' => $tipoDocumento,
                'ruta_archivo' => $ruta
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Finalizar
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('empleados.index')
        ->with(
            'success',
            'Empleado creado correctamente.'
        );
}



public function edit($dni)
{
    $empleado = Empleado::where('DNI', $dni)->firstOrFail();

    $departamentos = DepartamentoMuni::orderBy('codigo')->get();

    return view('empleados.edit', compact('empleado', 'departamentos'));
}

public function update(Request $request, $dni)
{
    $empleado = Empleado::where('DNI', $dni)->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Preparar beneficiarios opcionales
    |--------------------------------------------------------------------------
    */

    for ($i = 1; $i <= 7; $i++) {
        $request->merge([
            "nombre_beneficiario$i" =>
                $request->input("nombre_beneficiario$i") ?: 'Vacío',

            "porcentaje_beneficiario$i" =>
                $request->input("porcentaje_beneficiario$i") ?: 0,

            "parentezco_beneficiario$i" =>
                $request->input("parentezco_beneficiario$i") ?: 'Vacío',

            "DNI_beneficiario$i" =>
                $request->input("DNI_beneficiario$i") ?: '0000-0000-00000',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validar total de beneficiarios
    |--------------------------------------------------------------------------
    */

    $totalPorcentaje = 0;
    $hayBeneficiarios = false;

    for ($i = 1; $i <= 7; $i++) {

        $nombre = trim(
            (string) $request->input("nombre_beneficiario$i")
        );

        $porcentaje = (int) $request->input(
            "porcentaje_beneficiario$i",
            0
        );

        if ($nombre !== '' && $nombre !== 'Vacío') {
            $hayBeneficiarios = true;
            $totalPorcentaje += $porcentaje;
        }
    }

    if ($hayBeneficiarios) {

        if ($totalPorcentaje < 100) {

            $faltante = 100 - $totalPorcentaje;

            return back()
                ->withErrors([
                    'beneficiarios' =>
                        "Los porcentajes de beneficiarios suman {$totalPorcentaje}%. "
                        . "Falta asignar {$faltante}% para completar el 100%."
                ])
                ->withInput();
        }

        if ($totalPorcentaje > 100) {

            $excedente = $totalPorcentaje - 100;

            return back()
                ->withErrors([
                    'beneficiarios' =>
                        "Los porcentajes de beneficiarios suman {$totalPorcentaje}%. "
                        . "Excede el límite por {$excedente}%."
                ])
                ->withInput();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validaciones
    |--------------------------------------------------------------------------
    */

    $request->validate([

        // =========================
        // DATOS GENERALES
        // =========================

        'codigo' => [
            'required',
            'regex:/^[0-9]{1,4}$/'
        ],

        'DNI' => [
            'required',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{5}$/',
            Rule::unique('empleados', 'DNI')
                ->ignore($empleado->DNI, 'DNI'),
        ],

        'RTN' => [
            'required',
            'regex:/^[0-9]{4}-[0-9]{4}-[0-9]{6}$/'
        ],

        'primer_nombre' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'segundo_nombre' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'primer_apellido' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'segundo_apellido' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'sexo' => [
            'required',
            'in:Masculino,Femenino'
        ],

        'estado_civil' => [
            'required',
            'in:Soltero(a),Casado(a),Unión Libre,Divorciado(a),Viudo(a)'
        ],

        'nacionalidad' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'tipo_sangre' => [
            'required',
            'in:A+,A-,B+,B-,AB+,AB-,O+,O-'
        ],


        // =========================
        // FECHA DE NACIMIENTO
        // =========================

        'dia_nacimiento' => [
            'required',
            'integer',
            'min:1',
            'max:31'
        ],

        'mes_nacimiento' => [
            'required',
            'integer',
            'min:1',
            'max:12'
        ],

        'anio_nacimiento' => [
            'required',
            'integer',
            'min:1940',
            'max:' . (date('Y') - 18)
        ],


        // =========================
        // INFORMACIÓN DE CONTACTO
        // =========================

        'telefono_celular' => [
            'required',
            'regex:/^(3|8|9)[0-9]{7}$/'
        ],

        'telefono_fijo' => [
            'nullable',
            'regex:/^2[0-9]{7}$/'
        ],

        'direccion_domicilio' => [
            'required',
            'string',
            'max:255'
        ],

        'referencia_domicilio' => [
            'required',
            'string',
            'max:255'
        ],

        'nivel_educativo' => [
            'required',
            'in:Nivel Primario,Nivel Secundario,Nivel Superior,Postgrado'
        ],


        // =========================
        // CONTACTOS DE EMERGENCIA
        // =========================

        'nombre_contacto1' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'telefono_contacto1' => [
            'required',
            'regex:/^[0-9]{8}$/'
        ],

        'parentezco_contacto1' => [
            'required',
            'in:Padre,Madre,Hermano(a),Abuelo(a),Tío(a),Primo(a),Esposo(a),Pareja,Hijo(a),Amigo(a),Vecino(a),Otro'
        ],

        'nombre_contacto2' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'max:50'
        ],

        'telefono_contacto2' => [
            'nullable',
            'regex:/^[0-9]{8}$/'
        ],

        'parentezco_contacto2' => [
            'nullable',
            'in:Padre,Madre,Hermano(a),Abuelo(a),Tío(a),Primo(a),Esposo(a),Pareja,Hijo(a),Amigo(a),Vecino(a),Otro'
        ],


        // =========================
        // INFORMACIÓN LABORAL
        // =========================

        'puesto' => [
            'required',
            'regex:/^[\pL\s]+$/u',
            'min:3',
            'max:100'
        ],

        'puesto_funcional' => [
            'nullable',
            'regex:/^[\pL\s]+$/u',
            'min:3',
            'max:100'
        ],

        'fecha_nombramiento' => [
            'required',
            'date',
            'before_or_equal:today'
        ],

        'tipo' => [
            'required',
            'in:Acuerdo,Contrato'
        ],

        'salario_inicial' => [
            'required',
            'regex:/^L\.?\s?[0-9]{1,3}(,[0-9]{3})*(\.[0-9]{2})?$/'
        ],

        'departamento_id' => [
            'required',
            'exists:departamentos_muni,id'
        ],

        'fecha_fin_contrato' => [
            'nullable',
            'date',
            'after_or_equal:fecha_nombramiento',
            'required_if:tipo,Contrato'
        ],


        // =========================
        // DOCUMENTOS
        // =========================

        'copia_dni' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'copia_rtn' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'acuerdo' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

        'nota_traslado' => [
            'nullable',
            'file',
            'mimes:pdf,jpg,jpeg,png',
            'max:5120'
        ],

    ], [

        // =========================
        // CÓDIGO
        // =========================

        'codigo.required' =>
            'El código es obligatorio.',

        'codigo.regex' =>
            'El código debe contener entre 1 y 4 dígitos.',


        // =========================
        // DNI / RTN
        // =========================

        'DNI.required' =>
            'El DNI es obligatorio.',

        'DNI.regex' =>
            'El DNI debe tener el formato 0000-0000-00000.',

        'DNI.unique' =>
            'Este DNI ya está registrado.',

        'RTN.required' =>
            'El RTN es obligatorio.',

        'RTN.regex' =>
            'El RTN debe tener el formato 0000-0000-000000.',


        // =========================
        // NOMBRES Y APELLIDOS
        // =========================

        'primer_nombre.required' =>
            'El primer nombre es obligatorio.',

        'primer_nombre.regex' =>
            'El primer nombre solo puede contener letras y espacios.',

        'primer_nombre.max' =>
            'El primer nombre no puede superar los 50 caracteres.',

        'segundo_nombre.regex' =>
            'El segundo nombre solo puede contener letras y espacios.',

        'segundo_nombre.max' =>
            'El segundo nombre no puede superar los 50 caracteres.',

        'primer_apellido.required' =>
            'El primer apellido es obligatorio.',

        'primer_apellido.regex' =>
            'El primer apellido solo puede contener letras y espacios.',

        'primer_apellido.max' =>
            'El primer apellido no puede superar los 50 caracteres.',

        'segundo_apellido.regex' =>
            'El segundo apellido solo puede contener letras y espacios.',

        'segundo_apellido.max' =>
            'El segundo apellido no puede superar los 50 caracteres.',


        // =========================
        // FECHA DE NACIMIENTO
        // =========================

        'dia_nacimiento.required' =>
            'Debe seleccionar el día de nacimiento.',

        'dia_nacimiento.integer' =>
            'El día de nacimiento no es válido.',

        'dia_nacimiento.min' =>
            'El día de nacimiento no es válido.',

        'dia_nacimiento.max' =>
            'El día de nacimiento no es válido.',

        'mes_nacimiento.required' =>
            'Debe seleccionar el mes de nacimiento.',

        'mes_nacimiento.integer' =>
            'El mes de nacimiento no es válido.',

        'mes_nacimiento.min' =>
            'El mes de nacimiento no es válido.',

        'mes_nacimiento.max' =>
            'El mes de nacimiento no es válido.',

        'anio_nacimiento.required' =>
            'Debe seleccionar el año de nacimiento.',

        'anio_nacimiento.integer' =>
            'El año de nacimiento no es válido.',

        'anio_nacimiento.min' =>
            'El año de nacimiento no es válido.',

        'anio_nacimiento.max' =>
            'El empleado debe tener al menos 18 años.',


        // =========================
        // DATOS GENERALES
        // =========================

        'sexo.required' =>
            'Debe seleccionar el sexo.',

        'sexo.in' =>
            'El sexo seleccionado no es válido.',

        'estado_civil.required' =>
            'Debe seleccionar el estado civil.',

        'estado_civil.in' =>
            'El estado civil seleccionado no es válido.',

        'nacionalidad.required' =>
            'La nacionalidad es obligatoria.',

        'nacionalidad.regex' =>
            'La nacionalidad solo puede contener letras y espacios.',

        'nacionalidad.max' =>
            'La nacionalidad no puede superar los 50 caracteres.',

        'tipo_sangre.required' =>
            'Debe seleccionar el tipo de sangre.',

        'tipo_sangre.in' =>
            'El tipo de sangre seleccionado no es válido.',


        // =========================
        // CONTACTO
        // =========================

        'telefono_celular.required' =>
            'El teléfono celular es obligatorio.',

        'telefono_celular.regex' =>
            'El celular debe iniciar con 3, 8 o 9 y contener exactamente 8 dígitos.',

        'telefono_fijo.regex' =>
            'El teléfono fijo debe iniciar con 2 y contener exactamente 8 dígitos.',

        'direccion_domicilio.required' =>
            'La dirección del domicilio es obligatoria.',

        'direccion_domicilio.max' =>
            'La dirección del domicilio no puede superar los 255 caracteres.',

        'referencia_domicilio.required' =>
            'La referencia del domicilio es obligatoria.',

        'referencia_domicilio.max' =>
            'La referencia del domicilio no puede superar los 255 caracteres.',

        'nivel_educativo.required' =>
            'Debe seleccionar el nivel educativo.',

        'nivel_educativo.in' =>
            'El nivel educativo seleccionado no es válido.',


        // =========================
        // CONTACTOS DE EMERGENCIA
        // =========================

        'nombre_contacto1.required' =>
            'El nombre del primer contacto es obligatorio.',

        'nombre_contacto1.regex' =>
            'El nombre del primer contacto solo puede contener letras y espacios.',

        'nombre_contacto1.max' =>
            'El nombre del primer contacto no puede superar los 50 caracteres.',

        'telefono_contacto1.required' =>
            'El teléfono del primer contacto es obligatorio.',

        'telefono_contacto1.regex' =>
            'El teléfono del primer contacto debe contener exactamente 8 números.',

        'parentezco_contacto1.required' =>
            'Debe seleccionar el parentesco del primer contacto.',

        'parentezco_contacto1.in' =>
            'El parentesco del primer contacto no es válido.',

        'nombre_contacto2.regex' =>
            'El nombre del segundo contacto solo puede contener letras y espacios.',

        'nombre_contacto2.max' =>
            'El nombre del segundo contacto no puede superar los 50 caracteres.',

        'telefono_contacto2.regex' =>
            'El teléfono del segundo contacto debe contener exactamente 8 números.',

        'parentezco_contacto2.in' =>
            'El parentesco del segundo contacto no es válido.',


        // =========================
        // PUESTO
        // =========================

        'puesto.required' =>
            'El puesto de nombramiento es obligatorio.',

        'puesto.regex' =>
            'El puesto de nombramiento solo puede contener letras y espacios.',

        'puesto.min' =>
            'El puesto de nombramiento debe tener al menos 3 caracteres.',

        'puesto.max' =>
            'El puesto de nombramiento no puede superar los 100 caracteres.',

        'puesto_funcional.regex' =>
            'El puesto funcional solo puede contener letras y espacios.',

        'puesto_funcional.min' =>
            'El puesto funcional debe tener al menos 3 caracteres.',

        'puesto_funcional.max' =>
            'El puesto funcional no puede superar los 100 caracteres.',


        // =========================
        // INFORMACIÓN LABORAL
        // =========================

        'fecha_nombramiento.required' =>
            'Debe ingresar la fecha de nombramiento.',

        'fecha_nombramiento.date' =>
            'Debe ingresar una fecha de nombramiento válida.',

        'fecha_nombramiento.before_or_equal' =>
            'La fecha de nombramiento no puede ser futura.',

        'tipo.required' =>
            'Debe seleccionar el tipo de contrato.',

        'tipo.in' =>
            'El tipo de contrato seleccionado no es válido.',

        'fecha_fin_contrato.required_if' =>
            'Debe ingresar la fecha de finalización del contrato.',

        'fecha_fin_contrato.date' =>
            'La fecha de finalización del contrato no es válida.',

        'fecha_fin_contrato.after_or_equal' =>
            'La fecha de finalización del contrato no puede ser anterior a la fecha de nombramiento.',

        'salario_inicial.required' =>
            'El salario inicial es obligatorio.',

        'salario_inicial.regex' =>
            'El salario debe tener el formato L. 12,000.00.',

        'departamento_id.required' =>
            'Debe seleccionar un departamento.',

        'departamento_id.exists' =>
            'El departamento seleccionado no es válido.',


        // =========================
        // DOCUMENTOS
        // =========================

        'copia_dni.mimes' =>
            'La copia del DNI debe ser un archivo PDF, JPG, JPEG o PNG.',

        'copia_dni.max' =>
            'La copia del DNI no debe superar los 5 MB.',

        'copia_rtn.mimes' =>
            'La copia del RTN debe ser un archivo PDF, JPG, JPEG o PNG.',

        'copia_rtn.max' =>
            'La copia del RTN no debe superar los 5 MB.',

        'acuerdo.mimes' =>
            'El acuerdo o contrato debe ser un archivo PDF, JPG, JPEG o PNG.',

        'acuerdo.max' =>
            'El acuerdo o contrato no debe superar los 5 MB.',

        'nota_traslado.mimes' =>
            'La nota de traslado debe ser un archivo PDF, JPG, JPEG o PNG.',

        'nota_traslado.max' =>
            'La nota de traslado no debe superar los 5 MB.',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Validar que día, mes y año formen una fecha real
    |--------------------------------------------------------------------------
    */

    if (!checkdate(
        (int) $request->mes_nacimiento,
        (int) $request->dia_nacimiento,
        (int) $request->anio_nacimiento
    )) {
        return back()
            ->withErrors([
                'dia_nacimiento' =>
                    'La fecha de nacimiento seleccionada no es válida.'
            ])
            ->withInput();
    }


    /*
    |--------------------------------------------------------------------------
    | Preparar salario
    |--------------------------------------------------------------------------
    */

    if ($request->filled('salario_inicial')) {

        $salario = $request->salario_inicial;

        $salario = str_replace('L.', '', $salario);
        $salario = str_replace(' ', '', $salario);
        $salario = str_replace(',', '', $salario);

        $request->merge([
            'salario_inicial' => $salario
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Tipo de contrato
    |--------------------------------------------------------------------------
    */

    if ($request->tipo === 'Acuerdo') {
        $request->merge([
            'fecha_fin_contrato' => null
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Puesto funcional automático
    |--------------------------------------------------------------------------
    */

    if (empty($request->puesto_funcional)) {
        $request->merge([
            'puesto_funcional' => $request->puesto
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Preparar datos
    |--------------------------------------------------------------------------
    */

    $data = $request->except([
        '_token',
        '_method',
        'copia_dni',
        'copia_rtn',
        'acuerdo',
        'nota_traslado',
    ]);

    $data['usuario_modifica'] =
        auth()->user()->name ?? 'Sistema';

    $datosAnteriores = $empleado->toArray();


    /*
    |--------------------------------------------------------------------------
    | Actualizar empleado
    |--------------------------------------------------------------------------
    */

    $empleado->update($data);


    /*
    |--------------------------------------------------------------------------
    | Registrar bitácora
    |--------------------------------------------------------------------------
    */

    BitacoraHelper::registrar(
        'editar_empleado',
        'empleados',

        'Se actualizó el empleado: ' .
        $empleado->primer_nombre . ' ' .
        $empleado->primer_apellido,

        $empleado->DNI,

        'empleado',

        $datosAnteriores,

        $empleado->fresh()->toArray()
    );


    /*
    |--------------------------------------------------------------------------
    | Guardar nuevos documentos
    |--------------------------------------------------------------------------
    */

    $documentos = [
        'copia_dni' => 'Copia DNI',
        'copia_rtn' => 'Copia RTN',
        'acuerdo' => 'Acuerdo',
        'nota_traslado' => 'Nota Traslado',
    ];

    foreach ($documentos as $campo => $tipoDocumento) {

        if ($request->hasFile($campo)) {

            $ruta = $request->file($campo)
                ->store(
                    "empleados/{$empleado->DNI}/documentos",
                    'public'
                );

            DocumentoEmpleado::create([
                'dni_empleado' => $empleado->DNI,
                'tipo_documento' => $tipoDocumento,
                'ruta_archivo' => $ruta
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Finalizar
    |--------------------------------------------------------------------------
    */

    return redirect()
        ->route('empleados.verRegistro', $empleado->DNI)
        ->with(
            'success',
            'Empleado actualizado correctamente.'
        );
}


//show para ver movimientos y dias disponibles
public function show($dni)
{
	$empleado = Empleado::where('DNI', $dni)
		->with('departamentoFuncional')
		->firstOrFail();

	$periodosActivos = PeriodoVacacionesSistema::where('dni_empleado', $dni)
	->whereIn('estado', ['activo', 'extendido'])
	->orderByDesc('anio_laboral')
	->get();

    $periodosVencidos = PeriodoVacacionesSistema::where('dni_empleado', $dni)
	->where('estado', 'vencido')
	->orderByDesc('anio_laboral')
	->get();

	// 🔹 Movimientos
	// 🔹 Año seleccionado para historial
$anioSeleccionado = request('anio', now()->year);

// 🔹 Años disponibles en movimientos
$aniosMovimientos = MovimientoPermisoSistema::where('dni_empleado', $dni)
	->selectRaw('YEAR(created_at) as anio')
	->distinct()
	->orderByDesc('anio')
	->pluck('anio');

// 🔹 Movimientos filtrados
$movimientosQuery = MovimientoPermisoSistema::where('dni_empleado', $dni);

if ($anioSeleccionado !== 'todos') {
	$movimientosQuery->whereYear('created_at', $anioSeleccionado);
}

$movimientos = $movimientosQuery
	->orderByDesc('created_at')
	->get();

	// 🔥 Vacaciones disponibles
	$totalDiasDisponibles = $periodosActivos->sum(function ($periodo) {
		return max(0, $periodo->dias_otorgados - $periodo->dias_usados);
	});

	// 🔥 Compensatorios disponibles
	$diasCompensatorios = DB::table('compensatorios_sistema')
		->where('dni_empleado', $dni)
		->where('estado', 'activo')
		->sum('dias_disponibles');

	// 🔥 Total general
	$totalGeneral = $totalDiasDisponibles + $diasCompensatorios;

	// 🔥 Compensatorios por año
	$diasCompensatoriosPorAnio = DB::table('compensatorios_sistema')
		->selectRaw('YEAR(fecha_origen) as anio, SUM(dias_otorgados) as total')
		->where('dni_empleado', $dni)
		->groupBy('anio')
		->pluck('total', 'anio');

	// 🔥 Permisos relacionados con los movimientos
	$permisos = PermisoSistema::whereIn(
			'id',
			$movimientos->pluck('permiso_id')->filter()->unique()
		)
		->with(['tipo', 'estado'])
		->get()
		->keyBy('id');

        // 🔥 Horas disponibles
$horasDisponibles = DB::table('horas_acumuladas_sistema')
	->where('dni_empleado', $dni)
	->where('estado', 'activo')
	->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
	->value('total') ?? 0;

	return view('empleados.show', compact(
		'empleado',
		'periodosActivos',
		'periodosVencidos',
		'movimientos',
		'totalDiasDisponibles',
		'diasCompensatorios',
		'totalGeneral',
        'horasDisponibles',
		'diasCompensatoriosPorAnio',
        'anioSeleccionado',
'aniosMovimientos',
		'permisos'
    
	));
}




public function reporte($dni)
{
	$empleado = Empleado::where('DNI', $dni)
		->with('departamentoFuncional')
		->firstOrFail();

	// 🔥 ACTIVOS
	$periodosActivos = PeriodoVacacionesSistema::where('dni_empleado', $dni)
		->whereIn('estado', ['activo','extendido'])
		->orderByDesc('anio_laboral')
		->get();

	// 🔴 VENCIDOS
	$periodosVencidos = PeriodoVacacionesSistema::where('dni_empleado', $dni)
		->where('estado', 'vencido')
		->orderByDesc('anio_laboral')
		->get();

	// 📊 MOVIMIENTOS
	$movimientos = MovimientoPermisoSistema::where('dni_empleado', $dni)
		->orderByDesc('created_at')
		->get();

        // 📊 Movimientos agrupados por año para el PDF
$movimientosPorAnio = $movimientos->groupBy(function ($movimiento) {
	return \Carbon\Carbon::parse($movimiento->created_at)->format('Y');
});

	// 🔥 VACACIONES
	$totalDiasDisponibles = $periodosActivos->sum(function ($periodo) {
		return max(0, $periodo->dias_otorgados - $periodo->dias_usados);
	});

	// 🔥 COMPENSATORIOS
	$diasCompensatorios = DB::table('compensatorios_sistema')
		->where('dni_empleado', $dni)
		->where('estado', 'activo')
		->sum('dias_disponibles');

        // 🔥 HORAS DISPONIBLES
$horasDisponibles = DB::table('horas_acumuladas_sistema')
	->where('dni_empleado', $dni)
	->where('estado', 'activo')
	->selectRaw('SUM(horas_otorgadas - horas_usadas) as total')
	->value('total') ?? 0;

	// 🔥 TOTAL
	$totalGeneral = $totalDiasDisponibles + $diasCompensatorios;

	// 🔥 COMPENSATORIOS POR AÑO
	$diasCompensatoriosPorAnio = DB::table('compensatorios_sistema')
		->selectRaw('YEAR(fecha_origen) as anio, SUM(dias_otorgados) as total')
		->where('dni_empleado', $dni)
		->groupBy('anio')
		->pluck('total', 'anio');

	// 🔥 PERMISOS RELACIONADOS
	$permisos = PermisoSistema::whereIn(
			'id',
			$movimientos->pluck('permiso_id')->filter()->unique()
		)
		->with(['tipo','estado'])
		->get()
		->keyBy('id');

	// 📅 FECHA
	$fechaGeneracion = Carbon::now('America/Tegucigalpa')
		->locale('es')
		->translatedFormat('d \d\e F \d\e\l Y H:i');

	$pdf = Pdf::loadView('empleados.reporte', [
		'empleado' => $empleado,
		'periodosActivos' => $periodosActivos,
		'periodosVencidos' => $periodosVencidos,
		'movimientos' => $movimientos,
        'movimientosPorAnio' => $movimientosPorAnio,
		'totalDiasDisponibles' => $totalDiasDisponibles,
		'diasCompensatorios' => $diasCompensatorios,
		'totalGeneral' => $totalGeneral,
		'diasCompensatoriosPorAnio' => $diasCompensatoriosPorAnio,
		'permisos' => $permisos,
        'horasDisponibles' => $horasDisponibles,
		'fechaGeneracion' => $fechaGeneracion
	]);

	$pdf->setPaper('a4', 'portrait');
	$pdf->render();

	return $pdf->stream('reporte_empleado_'.$empleado->DNI.'.pdf', [
		'Attachment' => false
	]);
}





public function expediente($dni)
{
    $empleado = Empleado::with('documentos')
        ->where('DNI', $dni)
        ->firstOrFail();

    return view('empleados.expediente', compact('empleado'));
}



public function verRegistro($dni)
{
    $empleado = Empleado::with([
            'documentos',
            'departamento',
            'departamentoFuncional'
        ])
        ->where('DNI', $dni)
        ->firstOrFail();

    return view('empleados.verRegistro', compact('empleado'));
}


public function verRegistroImprimir($dni)
{
    $empleado = Empleado::with('documentos')
        ->where('DNI', $dni)
        ->firstOrFail();

    /*
    |--------------------------------------------------------------------------
    | Generar el PDF del registro del empleado
    |--------------------------------------------------------------------------
    */

    $pdf = Pdf::loadView(
        'empleados.verRegistroImprimir',
        compact('empleado')
    );

    $pdf->setPaper('letter');

    $tempPdf = storage_path(
        'app/temp_registro_' .
        $empleado->DNI . '_' .
        uniqid() .
        '.pdf'
    );

    file_put_contents(
        $tempPdf,
        $pdf->output()
    );

    /*
    |--------------------------------------------------------------------------
    | Crear el PDF final
    |--------------------------------------------------------------------------
    */

    $fpdi = new Fpdi();

    /*
    |--------------------------------------------------------------------------
    | Agregar el registro del empleado
    |--------------------------------------------------------------------------
    */

    $totalPaginas = $fpdi->setSourceFile($tempPdf);

    for ($i = 1; $i <= $totalPaginas; $i++) {

        $template = $fpdi->importPage($i);

        $size = $fpdi->getTemplateSize($template);

        $fpdi->AddPage(
            $size['orientation'],
            [$size['width'], $size['height']]
        );

        $fpdi->useTemplate($template);
    }

    /*
    |--------------------------------------------------------------------------
    | Ordenar documentos
    |--------------------------------------------------------------------------
    */

    $documentos = $empleado->documentos
        ->sortBy(function ($doc) {

            return match (mb_strtolower(trim($doc->tipo_documento))) {

                'copia dni'            => 1,
                'copia rtn'            => 2,
                'acuerdo',
                'contrato',
                'acuerdo / contrato'   => 3,
                'nota traslado',
                'nota de traslado'     => 4,

                default                => 99,
            };

        });

    /*
    |--------------------------------------------------------------------------
    | Agregar documentos al PDF
    |--------------------------------------------------------------------------
    */

    foreach ($documentos as $documento) {

        $ruta = storage_path(
            'app/public/' . $documento->ruta_archivo
        );

        if (!file_exists($ruta)) {
            continue;
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));

        // Solo anexar PDFs
        if ($extension !== 'pdf') {
            continue;
        }

        try {

            $paginasDocumento = $fpdi->setSourceFile($ruta);

            for ($pagina = 1; $pagina <= $paginasDocumento; $pagina++) {

                $template = $fpdi->importPage($pagina);

                $size = $fpdi->getTemplateSize($template);

                $fpdi->AddPage(
                    $size['orientation'],
                    [$size['width'], $size['height']]
                );

                $fpdi->useTemplate($template);

            }

        } catch (\Throwable $e) {

            // Si un documento está dañado, continuar con el siguiente.
            continue;

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar archivo temporal
    |--------------------------------------------------------------------------
    */

    if (file_exists($tempPdf)) {
        unlink($tempPdf);
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar PDF en el navegador
    |--------------------------------------------------------------------------
    */

    return response(
        $fpdi->Output(
            'S',
            'registro-empleado-' . $empleado->DNI . '.pdf'
        ),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' =>
                'inline; filename="registro-empleado-' .
                $empleado->DNI .
                '.pdf"',
        ]
    );
}


 


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }



    

    public function editarFuncion($dni)
    {
        $empleado = Empleado::findOrFail($dni);

        $departamentos = DepartamentoMuni::where('activo',1)
            ->orderBy('codigo')
            ->get();

        return view('empleados.funcion', compact('empleado','departamentos'));
    }

    public function guardarFuncion(Request $request, $dni)
    {
        $empleado = Empleado::findOrFail($dni);
        $departamentoAnterior = $empleado->departamento_funcional_id;

        $empleado->departamento_funcional_id =
            $request->departamento_funcional_id;

        $empleado->save();

                BitacoraHelper::registrar(
            'cambiar_funcion_empleado',
            'empleados',
            'Se actualizó el departamento funcional del empleado: ' .
            $empleado->primer_nombre . ' ' .
            $empleado->primer_apellido,

            $empleado->DNI,

            'empleado',

            [
                'departamento_funcional_id' => $departamentoAnterior
            ],

            [
                'departamento_funcional_id' => $empleado->departamento_funcional_id
            ]
        );

        return redirect()
            ->route('empleados.verRegistro',$dni)
            ->with('success','Asignación funcional actualizada');
    }



    //estado de empleado para inactivar o activar
    public function cambiarEstado($dni)
    {
        $empleado = Empleado::where('DNI', $dni)->firstOrFail();

        $estadoActual = strtolower(trim($empleado->estado_empleado ?? 'activo'));
        $estadoAnterior = $empleado->estado_empleado;

        $empleado->estado_empleado = $estadoActual === 'inactivo'
            ? 'activo'
            : 'inactivo';

        $empleado->usuario_modifica = auth()->user()->name ?? 'Sistema';
        $empleado->save();

        BitacoraHelper::registrar(
            $empleado->estado_empleado === 'activo'
                ? 'activar_empleado'
                : 'inactivar_empleado',

            'empleados',

            $empleado->estado_empleado === 'activo'
                ? 'Se activó el empleado: '
                : 'Se inactivó el empleado: ' .

            $empleado->primer_nombre . ' ' .
            $empleado->primer_apellido,

            $empleado->DNI,

            'empleado',

            [
                'estado_empleado' => $estadoAnterior
            ],

            [
                'estado_empleado' => $empleado->estado_empleado
            ]
        );

        $mensaje = $empleado->estado_empleado === 'activo'
            ? 'Empleado activado correctamente.'
            : 'Empleado inactivado correctamente.';

        return redirect()
            ->route('empleados.index')
            ->with('success', $mensaje);
    }


    public function imprimirListado(Request $request)
    {
        $estado = $request->get('estado', 'activo');

        $query = Empleado::with(['departamento', 'departamentoFuncional'])
            ->orderBy('primer_nombre')
            ->orderBy('primer_apellido');

        if ($estado === 'activo') {
            $query->where(function ($q) {
                $q->where('estado_empleado', 'activo')
                ->orWhereNull('estado_empleado');
            });
        }

        if ($estado === 'inactivo') {
            $query->where('estado_empleado', 'inactivo');
        }

        $empleados = $query->get();

        $titulo = match ($estado) {
            'inactivo' => 'Listado de Empleados Inactivos',
            'todos' => 'Listado General de Empleados',
            default => 'Listado de Empleados Activos',
        };

        $pdf = Pdf::loadView('empleados.imprimirListado', compact('empleados', 'titulo', 'estado'));

        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream('listado-empleados-'.$estado.'.pdf');
    }
}
