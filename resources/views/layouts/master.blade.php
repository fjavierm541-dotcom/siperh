<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema RRHH')</title>
    
     <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('isologosiperh.ico') }}">
    <link rel="shortcut icon" href="{{ asset('isologosiperh.ico') }}">
    

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1f3a56, #2d4f73);
            min-height: 100vh;
            padding-top: 75px; /* 👈 Espacio para navbar fijo */
        }

        .navbar-custom {
            background: linear-gradient(90deg, #1f3a56, #2d4f73);
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white;
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            color: #d4b06a;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 18px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }

        .btn-primary-custom {
            background-color: #1f3a56;
            border: none;
            color: white;
        }

        .btn-primary-custom:hover {
            background-color: #162a40;
        }

        .navbar-logo-siperh {
    width: 42px;
    height: 42px;
    object-fit: contain;
    filter: drop-shadow(0 6px 8px rgba(0,0,0,.25));
}

.notificacion-btn {
    position: relative;
    color: white;
    font-size: 20px;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 50%;
    transition: all .25s ease;
}

.notificacion-btn:hover {
    color: #d4b06a;
    background: rgba(255,255,255,.08);
}

.notificacion-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #dc3545;
    color: white;
    font-size: 10px;
    font-weight: bold;
    border-radius: 50%;
    min-width: 17px;
    height: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
}



        /* ---------- MICRO FEEDBACK INPUTS ---------- */

input, select, textarea {
    transition: border-color 0.25s ease, box-shadow 0.25s ease;
}

.field-valid {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.15rem rgba(40,167,69,.15);
}

.field-invalid {
    border-color: #dc3545 !important;
}

/* ---------- TRANSICIÓN DE PÁGINA ---------- */

.page-enter {
    animation: pageFade 0.25s ease;
}

/* ---------- MODAL SESIÓN ---------- */

.session-modal .modal-content{
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,.25);
}

.session-modal .modal-header{
    background: linear-gradient(135deg, #1f3a56, #2d4f73);
    color: white;
    border: none;
}

.session-modal .modal-body{
    padding: 24px;
}

.session-modal .modal-footer{
    border: none;
    padding: 0 24px 24px;
}

.btn-session{
    background: #1f3a56;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
}

.btn-session:hover{
    background: #162a40;
    color: white;
}

.dropdown-notificacion-texto {
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.35;
    max-width: 260px;
}

.dropdown-notificacion-item {
    transition: background .2s ease;
}

.dropdown-notificacion-item:hover {
    background: #f5f7fa;
}

.notificacion-tipo-info {
    border-left: 4px solid #0d6efd;
}

.notificacion-tipo-success {
    border-left: 4px solid #198754;
}

.notificacion-tipo-warning {
    border-left: 4px solid #ffc107;
}

.notificacion-tipo-danger {
    border-left: 4px solid #dc3545;
}

@keyframes pageFade {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>

<body>
    


<!-- NAVBAR SUPERIOR FIJO -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top shadow">
    <div class="container-fluid px-4">

        <!-- Nombre del sistema -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('paginas.inicio') }}">

    <img src="{{ asset('images/isologosiperh.png') }}"
         alt="SIPERH"
         class="navbar-logo-siperh">

    <div class="d-flex flex-column lh-sm">
        <span class="fw-bold">SIPERH</span>
        <small style="font-size: 11px; color: #dbeafe;">
            Sistema de Personal de Recursos Humanos
        </small>
    </div>

</a>

        <!-- Botón responsive -->
        <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- Links  -->
            <ul class="navbar-nav mx-auto">

            <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('paginas.inicio') ? 'active fw-bold text-warning' : '' }}"
                       href="{{ route('paginas.inicio') }}">
                        Inicio
                    </a>
                </li>

                @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('empleados.*') ? 'active fw-bold text-warning' : '' }}"
                    href="{{ route('empleados.index') }}">
                        Empleados
                    </a>
                </li>

            @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('permisos.*') ? 'active fw-bold text-warning' : '' }}"
                       href="{{ route('permisos.menu') }}">
                        Permisos
                    </a>
                </li>

                @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('departamentos.index', 'departamentos.show', 'departamentos.create', 'departamentos.edit') ? 'active fw-bold text-warning' : '' }}"
                    href="{{ route('departamentos.index') }}">
                        Departamentos
                    </a>
                </li>

            @endif

            @if(auth()->user()->rol === 'jefe_departamento')

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('departamentos.mi') ? 'active fw-bold text-warning' : '' }}"
                    href="{{ route('departamentos.mi') }}">
                        Mi departamento
                    </a>
                </li>

            @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('calendario.*') ? 'active fw-bold text-warning' : '' }}"
                       href="{{ route('calendario.index') }}">
                        Calendario
                    </a>
                </li>

            </ul>


                <!-- Notificaciones -->
    <ul class="navbar-nav me-3">

        <li class="nav-item dropdown">

            <a class="notificacion-btn"
            href="#"
            data-bs-toggle="dropdown"
            title="Notificaciones">

                🔔

                <span class="notificacion-badge d-none" id="notificacionBadge">
                    0
                </span>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow"
                style="width: 340px;"
                id="notificacionDropdown">

                <li>
                    <h6 class="dropdown-header">
                        Notificaciones
                    </h6>
                </li>

                <li>
                    <span class="dropdown-item text-muted">
                        Cargando...
                    </span>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a href="{{ route('notificaciones.index') }}"
                    class="dropdown-item text-center fw-bold">
                        Ver todas
                    </a>
                </li>

            </ul>

        </li>

    </ul>


          <!-- Usuario logueado -->
<ul class="navbar-nav">

    <li class="nav-item dropdown">

        <a class="nav-link dropdown-toggle d-flex flex-column text-end"
           href="#"
           data-bs-toggle="dropdown">

            <span>{{ auth()->user()->name ?? 'Usuario' }}</span>

            <small style="font-size: 11px; color: #dbeafe;">
                @if(auth()->user()?->rol === 'superadmin')
                    Super administrador
                @elseif(auth()->user()?->rol === 'rrhh')
                    Administrador RRHH
                @elseif(auth()->user()?->rol === 'jefe_departamento')
                    Jefe de departamento
                @else
                    Usuario
                @endif
            </small>

        </a>

        <ul class="dropdown-menu dropdown-menu-end">

            @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                <li>
                    <a class="dropdown-item" href="{{ route('usuarios.index') }}">
                        Usuarios y roles
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('correcciones-saldos.create') }}">
                        Corrección de saldos
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                        Control de vencimientos
                    </a>
                </li>

            @endif

            @if(auth()->user()->rol === 'superadmin')

                <li>
                    <a class="dropdown-item" href="{{ route('bitacora.index') }}">
                        Bitácora del sistema
                    </a>
                </li>

            @endif

            <li>
                <a class="dropdown-item" href="{{ route('configuracion.acerca') }}">
                    Acerca de SIPERH
                </a>
            </li>

            @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item" href="{{ route('configuracion.inicio') }}">
                        Panel de ajustes
                    </a>
                </li>

            @endif

            <li><hr class="dropdown-divider"></li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="dropdown-item text-danger">
                        Cerrar sesión
                    </button>

                </form>
            </li>

        </ul>

    </li>

</ul>


        </div>
    </div>
</nav>

<!-- CONTENIDO DINÁMICO -->
<div class="container py-4 page-enter">
    @yield('content')
</div>

<!-- MODAL SESIÓN -->
<div class="modal fade session-modal"
     id="sessionModal"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Sesión próxima a expirar
                </h5>
            </div>

            <div class="modal-body">

                <p class="mb-2">
                    Tu sesión se cerrará en 2 minutos por inactividad.
                </p>

                <small class="text-muted">
                    Guarda los cambios pendientes para evitar pérdida de información.
                </small>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="cerrarSesionManual()">
                    Cerrar sesión
                </button>

                <button type="button"
                        class="btn btn-session"
                        onclick="continuarSesion()">
                    Continuar sesión
                </button>

            </div>

        </div>
    </div>
</div>


<!-- Bootstrap JS -->
<script>

document.addEventListener("input", function(e){

    if(e.target.matches("input, select, textarea")){

        const field = e.target

        if(field.value.trim().length > 0){

            field.classList.remove("field-invalid")
            field.classList.add("field-valid")

        }else{

            field.classList.remove("field-valid")
            field.classList.add("field-invalid")

        }

    }

})

</script>
<!-- LOADER -->
<script>

window.addEventListener("load", function(){

    const loader = document.getElementById("page-loader")

   if (loader) {
        loader.style.opacity = "0"

     setTimeout(function(){
            loader.style.display = "none"
        },300)
    }

})
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    cargarNotificaciones();

    setInterval(cargarNotificaciones, 60000);

});

const dropdownNotificaciones = document.querySelector(
    '[data-bs-toggle="dropdown"][title="Notificaciones"]'
);

dropdownNotificaciones.addEventListener('shown.bs.dropdown', function () {

    fetch("{{ route('notificaciones.marcarLeidas') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        }
    })
    .then(() => {

        const badge = document.getElementById('notificacionBadge');

        badge.textContent = '0';
        badge.classList.add('d-none');

    });

});

function cargarNotificaciones() {

    fetch("{{ route('notificaciones.recientes') }}")
        .then(response => response.json())
        .then(data => {

            const badge = document.getElementById('notificacionBadge');
            const dropdown = document.getElementById('notificacionDropdown');

            if (data.no_leidas > 0) {
                badge.textContent = data.no_leidas;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            let html = `
                <li>
                    <h6 class="dropdown-header">
                        Notificaciones
                    </h6>
                </li>
            `;

            if (data.notificaciones.length === 0) {

                html += `
                    <li>
                        <span class="dropdown-item text-muted">
                            No hay notificaciones.
                        </span>
                    </li>
                `;

            } else {

                data.notificaciones.forEach(item => {

                    html += `
                        <li>

                            <a href="/notificaciones/${item.id}/abrir"
                            class="dropdown-item dropdown-notificacion-item py-3 notificacion-tipo-${item.tipo}"

                                <div class="fw-bold small mb-1 dropdown-notificacion-texto">
                                    ${item.titulo}
                                </div>

                                <div class="text-muted small dropdown-notificacion-texto">
                                    ${item.mensaje}
                                </div>

                            </a>

                        </li>
                    `;

                });

            }

            html += `
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('notificaciones.index') }}"
                       class="dropdown-item text-center fw-bold">
                        Ver todas
                    </a>
                </li>
            `;

            dropdown.innerHTML = html;

        })
        .catch(() => {
            // No interrumpimos el sistema si falla.
        });

}
</script>

<script>

/* ==========================
   CONTROL DE SESIÓN
========================== */

let tiempoInactividad = 0
//tiempo de actividad y tiempo en el que aparecera el modal 
const LIMITE = 30 * 60 // 30 minutos
const AVISO = 28 * 60 // 28 minutos

let modalSesion = null
let modalMostrado = false

function reiniciarActividad(){

    // Si el modal ya está mostrado, NO lo cerramos automáticamente.
    // Así el usuario puede escoger "Continuar sesión" o "Cerrar sesión".
    if(modalMostrado){
        return
    }

    tiempoInactividad = 0

}

const eventosActividad = [
    "mousemove",
    "mousedown",
    "keypress",
    "scroll",
    "touchstart",
    "input",
    "click"
]

eventosActividad.forEach(evento => {

    document.addEventListener(evento, reiniciarActividad)

})

window.addEventListener("load", function(){

    const modalElement = document.getElementById("sessionModal")

    modalSesion = new bootstrap.Modal(modalElement)

})

setInterval(() => {

    tiempoInactividad++

    /* AVISO 2 MINUTOS ANTES */

    if(tiempoInactividad >= AVISO && !modalMostrado){

        modalSesion.show()
        modalMostrado = true

    }

    /* CERRAR SESIÓN */

    if(tiempoInactividad >= LIMITE){

    cerrarSesionManual()

}

}, 1000)

/* ==========================
   CONTINUAR SESIÓN
========================== */

function continuarSesion(){

    fetch("{{ route('session.keepalive') }}", {

        method: "POST",

        headers: {
            "X-CSRF-TOKEN":
                document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Accept": "application/json",
            "Content-Type": "application/json"
        }

    })
    .then(() => {

        tiempoInactividad = 0
        modalSesion.hide()
        modalMostrado = false

    })

}

/* ==========================
   LOGOUT MANUAL
========================== */

function cerrarSesionManual(){

    const form = document.createElement("form")

    form.method = "POST"
    form.action = "{{ route('logout') }}"

    const token = document.createElement("input")

    token.type = "hidden"
    token.name = "_token"
    token.value =
        document.querySelector('meta[name="csrf-token"]').getAttribute("content")

    form.appendChild(token)

    document.body.appendChild(form)

    form.submit()

}

</script>

</body>
</html>
