@extends('layouts.configuracion')

@section('title', 'Acerca de SIPERH')

@section('config-content')

<style>
    .about-logo {
        max-width: 280px;
        width: 100%;
        height: auto;
        margin-top: 10px;
        margin-bottom: 24px;
        transition: transform 0.3s ease, filter 0.3s ease;
        filter: drop-shadow(0 12px 18px rgba(0,0,0,0.16));
    }

    .about-logo:hover {
        transform: translateY(-4px) scale(1.02);
        filter: drop-shadow(0 18px 26px rgba(0,0,0,0.22));
    }

    .about-section-title {
        color: #1f3a56;
        font-weight: 700;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .about-info-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.96), rgba(241,244,248,0.9));
        border: 1px solid rgba(255,255,255,0.45);
        border-radius: 16px;
        padding: 22px;
        height: 100%;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .about-info-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow:
            0 18px 34px rgba(0,0,0,0.16),
            0 0 0 1px rgba(212,176,106,0.4);
        border: 1px solid rgba(212,176,106,0.65);
    }

    .about-info-card::before {
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

    .about-info-card:hover::before {
        left: 100%;
    }

    .about-info-card::after {
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

    .about-info-card:hover::after {
        opacity: 1;
        width: 90px;
    }

    .about-info-card:hover .about-section-title {
        color: #d4b06a;
        transition: 0.3s;
    }

    .about-muted {
        color: #6c757d;
        font-size: 14px;
    }

    .version-badge {
        background: #d4b06a;
        color: #1f3a56;
        font-weight: 700;
        border-radius: 50px;
        padding: 8px 16px;
        display: inline-block;
        box-shadow: 0 8px 18px rgba(212,176,106,.25);
    }

    .tech-badge {
        border-radius: 50px;
        padding: 7px 13px;
        font-size: 13px;
        font-weight: 600;
    }
</style>

<div class="glass-card overflow-hidden">

    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background: #27496d;">

        <div>
            <h4 class="text-white fw-bold mb-0">
                Acerca de SIPERH
            </h4>

            <small class="text-white-50">
                Información general del sistema.
            </small>
        </div>

    </div>

    <div class="p-4">

        <div class="text-center mb-4">

            <img src="{{ asset('images/logosiperh.png') }}"
                 alt="Logo SIPERH"
                 class="about-logo">

            <h3 class="fw-bold mb-2" style="color:#1f3a56;">
                Sistema de Personal de Recursos Humanos
            </h3>

            <p class="text-muted mb-3">
                Sistema desarrollado para la gestión administrativa de empleados,
                permisos, vacaciones y procesos internos de recursos humanos.
            </p>

            <span class="version-badge">
                Versión 1.0 · 2026
            </span>

        </div>

        <hr class="my-4">

        <div class="row g-4">

            <div class="col-md-6">
                <div class="about-info-card">
                    <div class="about-section-title mb-2">
                        Institución
                    </div>

                    <p class="mb-1 fw-bold">
                        Municipalidad de Danlí
                    </p>

                    <p class="about-muted mb-0">
                        Departamento de Recursos Humanos
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="about-info-card">
                    <div class="about-section-title mb-2">
                        Diseño y desarrollo
                    </div>

                    <p class="mb-1 fw-bold">
                        F. Javier Medina
                    </p>

                    <p class="about-muted mb-0">
                        Proyecto desarrollado durante práctica profesional UNAH.
                    </p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="about-info-card">
                    <div class="about-section-title mb-2">
                        Tecnologías
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-dark tech-badge">
                            Laravel
                        </span>

                        <span class="badge bg-primary tech-badge">
                            Bootstrap 5
                        </span>

                        <span class="badge bg-secondary tech-badge">
                            MySQL
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="about-info-card">
                    <div class="about-section-title mb-2">
                        Propósito
                    </div>

                    <p class="about-muted mb-0">
                        Fortalecer el control de personal, la trazabilidad administrativa
                        y la gestión de procesos internos del área de Recursos Humanos.
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>

@endsection