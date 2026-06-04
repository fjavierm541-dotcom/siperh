<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\PeriodoVacacionesController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartamentoMuniController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\SolicitudCompensatorioController;
use App\Http\Controllers\CorreccionSaldoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\BitacoraSistemaController;
use App\Http\Controllers\NotificacionSistemaController;
use App\Http\Controllers\PracticanteController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* ==========================
   AUTENTICACIÓN
========================== */

Route::get('/', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/* ==========================
   RUTAS PROTEGIDAS
========================== */
//GRUPO GENERAL 
Route::middleware(['auth', 'forzar.password', 'no.cache'])->group(function () {

    //MANTENER SESION INICIADA
    Route::post('/mantener-sesion', [LoginController::class, 'mantenerSesion'])
        ->name('session.keepalive');

    Route::get('/inicio', function () {
        return view('paginas.inicio');
    })->name('paginas.inicio');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

          // CAMBIAR PASSWORD
    Route::get('/cambiar-password', [LoginController::class, 'mostrarCambiarPassword'])
    ->name('password.cambiar');
            // RUTA PARA ACTUALIZAR PASSWORD
    Route::post('/cambiar-password', [LoginController::class, 'actualizarPassword'])
    ->name('password.actualizar');
            //MI DEPARTAMENTO
    Route::get('/mi-departamento', [DepartamentoMuniController::class, 'miDepartamento'])
    ->name('departamentos.mi');

    //RUTAS GENERALES PARA CALENDARIO
    Route::get('/calendario', [CalendarioController::class, 'index'])
    ->name('calendario.index');

    Route::get('/calendario/eventos', [CalendarioController::class, 'eventos']);

    Route::get('/calendario/dia', [CalendarioController::class, 'dia']); 


        /* ==========================
    PERMISOS - GENERAL
    ========================== */

    Route::get('/permisos/menu', function () {
        return view('permisos.menu');
    })->name('permisos.menu');

    Route::get('/permisos/crear', [PermisoController::class, 'create'])
        ->name('permisos.create');

    Route::post('/permisos', [PermisoController::class, 'store'])
        ->name('permisos.store');

    Route::get('/permisos/{id}/imprimir', [PermisoController::class, 'imprimir'])
        ->name('permisos.imprimir');

        //Mis permisos 
    Route::get('/mis-permisos', [PermisoController::class, 'misPermisos'])
        ->name('permisos.mis');

    Route::patch('/permisos/{id}/cancelar', [PermisoController::class, 'cancelar'])
        ->name('permisos.cancelar');

        /* ==========================
        COMPENSATORIOS - GENERAL
        ========================== */

        Route::get('/compensatorios/solicitudes/create', [SolicitudCompensatorioController::class, 'create'])
            ->name('compensatorios.solicitudes.create');

        Route::post('/compensatorios/solicitudes', [SolicitudCompensatorioController::class, 'store'])
            ->name('compensatorios.solicitudes.store');

        Route::get('/compensatorios/solicitudes/{id}', [SolicitudCompensatorioController::class, 'show'])
            ->name('compensatorios.solicitudes.show');

        Route::get('/compensatorios/solicitudes/{id}/imprimir', [SolicitudCompensatorioController::class, 'imprimir'])
            ->name('compensatorios.solicitudes.imprimir');

        Route::get('/empleados/por-departamento/{id}', function ($id) {
            return DB::table('empleados')
                ->where('departamento_funcional_id', $id)
                ->select(
                    'DNI',
                    DB::raw("CONCAT(primer_nombre, ' ', primer_apellido) as nombre")
                )
                ->get();
        });
        //mis compensatorios

    Route::get('/compensatorios/mis-compensatorios', [SolicitudCompensatorioController::class, 'misSolicitudes'])
    ->name('compensatorios.solicitudes.mis');

    Route::get('/compensatorios/solicitudes/{id}', [SolicitudCompensatorioController::class, 'show'])
    ->name('compensatorios.solicitudes.show');

    Route::patch('/compensatorios/{id}/cancelar', [SolicitudCompensatorioController::class, 'cancelar'])
    ->name('compensatorios.solicitudes.cancelar');



// GRUPO 2 - SOLO SUPERADMIN Y RRHH
    Route::middleware(['rol:superadmin,rrhh'])->group(function () {

    //
     //practicantes           
    Route::resource('practicantes', PracticanteController::class)
        ->middleware('auth');

                Route::patch(
            '/practicantes/{practicante}/toggle',
            [PracticanteController::class, 'toggle']
        )->name('practicantes.toggle');

        Route::get(
            '/practicantes/imprimir/{tipo}',
            [PracticanteController::class, 'imprimir']
        )->name('practicantes.imprimir');
            

        Route::get('/usuarios', [UsuarioController::class, 'index'])
            ->name('usuarios.index');

        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
            ->name('usuarios.create');

        Route::post('/usuarios', [UsuarioController::class, 'store'])
            ->name('usuarios.store');

        Route::patch('/usuarios/{id}/toggle', [UsuarioController::class, 'toggle'])
            ->name('usuarios.toggle');

        Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])
            ->name('usuarios.edit');

        Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])
            ->name('usuarios.update');

        Route::patch('/usuarios/{id}/reset-password',
            [UsuarioController::class, 'resetPassword'])
            ->name('usuarios.resetPassword');


        /* ==========================
        EMPLEADOS
        ========================== */

        Route::get('/empleados/imprimir/listado', [EmpleadoController::class, 'imprimirListado'])
            ->name('empleados.imprimirListado');

        Route::post('/empleados/{dni}/cambiar-estado', [EmpleadoController::class, 'cambiarEstado'])
            ->where('dni', '.*')
            ->name('empleados.cambiarEstado');

        Route::get('/empleados/create', [EmpleadoController::class, 'create'])
            ->name('empleados.create');

        Route::post('/empleados', [EmpleadoController::class, 'store'])
            ->name('empleados.store');

        Route::get('/empleados', [EmpleadoController::class, 'index'])
            ->name('empleados.index');

        Route::get('/empleados/{dni}/editar', [EmpleadoController::class, 'edit'])
            ->where('dni', '.*')
            ->name('empleados.edit');

        Route::put('/empleados/{dni}', [EmpleadoController::class, 'update'])
            ->where('dni', '.*')
            ->name('empleados.update');

        Route::get('/empleados/{dni}/expediente', [EmpleadoController::class, 'expediente'])
            ->where('dni', '.*')
            ->name('empleados.expediente');

        Route::get('/empleados/{dni}/ver-registro', [EmpleadoController::class, 'verRegistro'])
            ->where('dni', '.*')
            ->name('empleados.verRegistro');

        Route::get('/empleados/{dni}/imprimir', [EmpleadoController::class, 'verRegistroImprimir'])
            ->where('dni', '.*')
            ->name('empleados.verRegistro.imprimir');

        Route::get('/empleados/{dni}/reporte', [EmpleadoController::class, 'reporte'])
            ->where('dni', '.*')
            ->name('empleados.reporte');

        Route::get('/empleados/{dni}/funcion', [EmpleadoController::class, 'editarFuncion'])
            ->where('dni', '.*')
            ->name('empleados.funcion');

        Route::post('/empleados/{dni}/funcion', [EmpleadoController::class, 'guardarFuncion'])
            ->where('dni', '.*')
            ->name('empleados.funcion.guardar');

        Route::get('/empleados/{dni}', [EmpleadoController::class, 'show'])
            ->where('dni', '.*')
            ->name('empleados.show');


        /* ==========================
        DEPARTAMENTOS
        ========================== */

        Route::resource('departamentos', DepartamentoMuniController::class);

        Route::patch('/departamentos/{id}/toggle', [DepartamentoMuniController::class, 'toggle'])
            ->name('departamentos.toggle');

        Route::get('/departamentos/buscar', [DepartamentoMuniController::class, 'buscar'])
            ->name('departamentos.buscar');

        Route::get('/departamentos/{id}/asignar', [DepartamentoMuniController::class, 'asignar'])
            ->name('departamentos.asignar');

        Route::post('/departamentos/{id}/asignar', [DepartamentoMuniController::class, 'guardarAsignacion'])
            ->name('departamentos.asignar.guardar');

        Route::get('/departamentos/imprimir/{estado}', [DepartamentoMuniController::class, 'imprimir'])
            ->name('departamentos.imprimir');

        Route::patch('/departamentos/{departamento}/retirar-empleado/{empleado}', [DepartamentoMuniController::class, 'retirarEmpleado'])
            ->name('departamentos.retirarEmpleado');

        Route::get('/departamentos/{id}/imprimir-empleados', [DepartamentoMuniController::class, 'imprimirEmpleados'])
            ->name('departamentos.imprimirEmpleados');

        Route::get('/departamentos/{id}/jefe', [DepartamentoMuniController::class, 'editarJefe'])
            ->name('departamentos.jefe');

        Route::post('/departamentos/{id}/jefe', [DepartamentoMuniController::class, 'guardarJefe'])
            ->name('departamentos.jefe.guardar');


            //CALENDARIO PARA ADMINISTRADORES 
        Route::get('/calendario/create', [CalendarioController::class, 'create'])
                ->name('calendario.create');

        Route::post('/calendario/store', [CalendarioController::class, 'store'])
                ->name('calendario.store');

        Route::get('/calendario/{id}/edit', [CalendarioController::class, 'edit'])
                ->name('calendario.edit');

        Route::put('/calendario/{id}', [CalendarioController::class, 'update'])
                ->name('calendario.update');

        Route::delete('/calendario/{id}', [CalendarioController::class, 'destroy'])
                ->name('calendario.destroy');

        Route::get('/calendario/importar-feriados/{year}', [CalendarioController::class, 'importarFeriados'])
                ->name('calendario.importar');


            /* ==========================
            PERIODOS / VACACIONES
            ========================== */

        Route::get('/periodos/create', [PeriodoVacacionesController::class, 'create'])
            ->name('periodos.create');

        Route::post('/periodos/store', [PeriodoVacacionesController::class, 'store'])
            ->name('periodos.store');

        Route::post('/periodos/reactivar', [PeriodoVacacionesController::class, 'reactivar'])
            ->name('periodos.reactivar');

        Route::post('/vacaciones/generar', [EmpleadoController::class, 'generarVacaciones'])
            ->name('vacaciones.generar');

            /* ==========================
            PERMISOS 
            ========================== */

            Route::get('/permisos', [PermisoController::class, 'index'])
                ->name('permisos.index');

            Route::post('/permisos/{id}/aprobar', [PermisoController::class, 'aprobar'])
                ->name('permisos.aprobar');

            Route::post('/permisos/{id}/rechazar', [PermisoController::class, 'rechazar'])
                ->name('permisos.rechazar');

            Route::get('/permisos/imprimir-mes', [PermisoController::class, 'imprimirMes'])
                ->name('permisos.imprimir.mes');


                /* ==========================
            COMPENSATORIOS 
            ========================== */

            Route::get('/compensatorios/solicitudes', [SolicitudCompensatorioController::class, 'index'])
                ->name('compensatorios.solicitudes.index');

            Route::get('/compensatorios/solicitudes/imprimir-mes', [SolicitudCompensatorioController::class, 'imprimirMes'])
                ->name('compensatorios.solicitudes.imprimir.mes');

            Route::post('/compensatorios/{id}/aprobar', [SolicitudCompensatorioController::class, 'aprobar'])
                ->name('compensatorios.solicitudes.aprobar');

            Route::post('/compensatorios/{id}/rechazar', [SolicitudCompensatorioController::class, 'rechazar'])
                ->name('compensatorios.solicitudes.rechazar');


                    /* ==========================
       AJUSTES / CORRECCIÓN DE SALDOS
    ========================== */

    Route::get('/ajustes/correccion-saldos', [CorreccionSaldoController::class, 'create'])
        ->name('correcciones-saldos.create');

    Route::post('/ajustes/correccion-saldos', [CorreccionSaldoController::class, 'store'])
        ->name('correcciones-saldos.store');
                

        /*Route::get('/configuracion', function () {
            return view('configuracion.inicio');
        })->name('configuracion.inicio');*/ 

        Route::get('/ajustes', function () {
            return view('ajustes.inicio');
        })->name('configuracion.inicio');

        Route::get('/periodos/faltante', [CorreccionSaldoController::class, 'createPeriodoFaltante'])
    ->name('periodos.faltante.create');

    Route::post('/periodos/faltante', [CorreccionSaldoController::class, 'storePeriodoFaltante'])
        ->name('periodos.faltante.store');
                    

        });



    Route::post('/ajustes/correccion-saldos', [CorreccionSaldoController::class, 'store'])
        ->name('correcciones-saldos.store');


    /* ==========================
    BITÁCORA DEL SISTEMA
    ========================== */

Route::middleware(['rol:superadmin'])->group(function () {

    Route::get('/bitacora', [BitacoraSistemaController::class, 'index'])
        ->name('bitacora.index');

    Route::get('/bitacora/imprimir', [BitacoraSistemaController::class, 'imprimir'])
        ->name('bitacora.imprimir');

});
    


  Route::get('/ajustes/acerca-de', function () {
        return view('ajustes.acerca');
    })->name('configuracion.acerca');

         //notificaciones

    Route::get('/notificaciones', [NotificacionSistemaController::class, 'index'])
        ->name('notificaciones.index');

    Route::get('/notificaciones/recientes', [NotificacionSistemaController::class, 'recientes'])
        ->name('notificaciones.recientes');


        Route::get('/notificaciones/{id}/abrir', [NotificacionSistemaController::class, 'abrir'])
    ->name('notificaciones.abrir');

    Route::post('/notificaciones/marcar-leidas', [NotificacionSistemaController::class, 'marcarTodasLeidas'])
    ->name('notificaciones.marcarLeidas');




    /* ==========================
       PRUEBAS / UTILIDADES
    ========================== */

    Route::get('/prueba', function () {
        return view('paginas.prueba');
    });

    Route::get('/db-test', function () {
        try {
            DB::connection()->getPdo();
            return "Conexión a la base de datos exitosa.";
        } catch (\Exception $e) {
            return "Error de conexión: " . $e->getMessage();
        }
    });


Route::get('/test-419', function () {
    abort(419);
});

Route::get('/test-404', function () {
    abort(404);
});

Route::get('/test-500', function () {
    abort(500);
});

Route::get('/test-503', function () {
    abort(503);
});

Route::get('/test-504', function () {
    abort(504);
});

});

//falta agregar a ajustes
// 1 usuarios
// 2 correcciones de saldos
// 3 bitacoras
/*
\App\Models\User::create([
'empleado_dni' => '0703-1948-00285',
'name' => 'Javier Medina',
'username' => 'javier',
'email' => 'javier@gmail.com',
'telefono' => '98765432',
'password' => 'Admin2026*',
'debe_cambiar_password' => false,
'rol' => 'superadmin',
'activo' => true,
]);
*/


/*contraseña de Nega
Nega0703*
*/



/*

INSERT INTO tipos_permiso_sistema
(nombre, activo, created_at, updated_at)
VALUES
('Incapacidad', 1, NOW(), NOW());

*/