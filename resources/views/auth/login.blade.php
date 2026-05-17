@extends('layouts.auth')

@section('title', 'SIPER | Inicio de sesión')

@section('content')

<div class="login-card">

    <img src="/images/logosiperh.png" class="logo" alt="Logo SIPER">

    <div class="login-title">
        SIPER
    </div>

    <div class="login-subtitle">
        Sistema de Personal y Recursos Humanos
    </div>

    <p class="login-text">
        Plataforma institucional para el control y gestión integral del personal y procesos de recursos humanos.
    </p>

    @if ($errors->any())
        <div class="alert alert-danger alert-login">
            Las credenciales ingresadas no son válidas.
        </div>
    @endif

    <p class="attempts-text">
        Tienes <strong>10 intentos</strong> antes del bloqueo.
    </p>

    <form method="POST" action="{{ route('login.post') }}" id="loginForm" novalidate>
        @csrf

        <input type="text"
               id="usuario"
               name="username"
               class="form-control mb-1"
               placeholder="Usuario">

        <div id="errorUsuario" class="error-text"></div>

        <div class="password-wrapper mb-1">
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Contraseña">

            <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <div id="errorPassword" class="error-text"></div>

        <button id="btnLogin" class="btn-login mt-2" disabled>
            Iniciar sesión
        </button>

    </form>

    <a href="#"
       class="link d-block mt-2"
       data-bs-toggle="modal"
       data-bs-target="#modalPassword">
        ¿Olvidaste tu contraseña?
    </a>

    <div class="info-box"
         data-bs-toggle="modal"
         data-bs-target="#modalSistema">
        Conoce más sobre el sistema +
    </div>

    <div class="login-footer">
        © 2026 SIPER
    </div>

</div>

<!-- MODAL RECUPERACIÓN -->
<div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Recuperación de contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>
                    Para restablecer su contraseña debe presentarse en la oficina de Recursos Humanos.
                </p>

                <p>
                    El personal autorizado le proporcionará una contraseña temporal de uso único,
                    la cual deberá actualizar inmediatamente después de iniciar sesión.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal" data-bs-dismiss="modal">
                    Entendido
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL SISTEMA -->
<div class="modal fade" id="modalSistema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Acerca de SIPER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>
                    SIPER es una plataforma desarrollada para facilitar la administración del personal,
                    control de permisos, vacaciones, calendarios laborales y procesos internos de recursos humanos.
                </p>

                <div class="system-info">
                    <strong>Información del sistema</strong><br>
                    Versión 1.0<br>
                    Mayo 2026<br>
                    Desarrollado para la Municipalidad de Danlí.<br><br>
                    Desarrollo y soporte técnico:<br>
                    <strong>F. Javier Medina</strong>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-modal" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

@endsection