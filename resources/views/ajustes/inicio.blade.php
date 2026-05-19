@extends('layouts.configuracion')

@section('title', 'Ajustes')

@section('config-content')

<style>
    .ajustes-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.96), rgba(241,244,248,0.9));
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.45);
        transition: all 0.3s ease;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .ajustes-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow:
            0 18px 34px rgba(0,0,0,0.16),
            0 0 0 1px rgba(212,176,106,0.4);
        border: 1px solid rgba(212,176,106,0.65);
    }

    .ajustes-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            120deg,
            transparent,
            rgba(255,255,255,0.55),
            transparent
        );
        transition: 0.6s;
        pointer-events: none;
    }

    .ajustes-card:hover::before {
        left: 100%;
    }

    .ajustes-card::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 45px;
        height: 4px;
        background: #d4b06a;
        border-radius: 10px;
        opacity: 0;
        transition: 0.3s;
    }

    .ajustes-card:hover::after {
        opacity: 1;
        width: 90px;
    }

    .ajustes-card h5 {
        transition: 0.3s;
    }

    .ajustes-card:hover h5 {
        color: #d4b06a !important;
    }

    .ajustes-link {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .ajustes-link:hover {
        text-decoration: none;
        color: inherit;
    }

    .ajustes-icon {
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .ajustes-card:hover .ajustes-icon {
        transform: translateY(-3px) scale(1.08);
    }
</style>

<div class="glass-card overflow-hidden">

    <div class="p-4 text-white" style="background:#27496d;">
        <h4 class="fw-bold mb-1">Ajustes del sistema</h4>
        <small>Administra usuarios, auditoría, saldos y controles generales de SIPERH.</small>
    </div>

    <div class="p-4">

        <div class="row g-4">

            <div class="col-md-6">
                <a href="{{ route('usuarios.index') }}" class="ajustes-link">
                    <div class="ajustes-card p-4">
                        <h5 class="fw-bold text-dark">
                            <span class="ajustes-icon">👥</span>
                            Usuarios y roles
                        </h5>

                        <p class="text-muted mb-0">
                            Gestiona usuarios, roles, estados y contraseñas.
                        </p>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                <a href="{{ route('correcciones-saldos.create') }}" class="ajustes-link">
                    <div class="ajustes-card p-4">
                        <h5 class="fw-bold text-dark">
                            <span class="ajustes-icon">🛠</span>
                            Corrección de saldos
                        </h5>

                        <p class="text-muted mb-0">
                            Ajusta saldos de vacaciones, compensatorios y horas.
                        </p>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                <a href="{{ route('dashboard') }}" class="ajustes-link">
                    <div class="ajustes-card p-4">
                        <h5 class="fw-bold text-dark">
                            <span class="ajustes-icon">🚦</span>
                            Control de vencimientos
                        </h5>

                        <p class="text-muted mb-0">
                            Consulta empleados con vacaciones próximas a vencer.
                        </p>
                    </div>
                </a>
            </div>

            @if(auth()->user()->rol === 'superadmin')
                <div class="col-md-6">
                    <a href="{{ route('bitacora.index') }}" class="ajustes-link">
                        <div class="ajustes-card p-4">
                            <h5 class="fw-bold text-dark">
                                <span class="ajustes-icon">📘</span>
                                Bitácora del sistema
                            </h5>

                            <p class="text-muted mb-0">
                                Revisa acciones importantes realizadas en el sistema.
                            </p>
                        </div>
                    </a>
                </div>
            @endif

            <div class="col-md-6">
                <a href="{{ route('configuracion.acerca') }}" class="ajustes-link">
                    <div class="ajustes-card p-4">
                        <h5 class="fw-bold text-dark">
                            <span class="ajustes-icon">ℹ️</span>
                            Acerca de SIPERH
                        </h5>

                        <p class="text-muted mb-0">
                            Consulta información institucional, versión y créditos del sistema.
                        </p>
                    </div>
                </a>
            </div>

        </div>

    </div>

</div>

@endsection