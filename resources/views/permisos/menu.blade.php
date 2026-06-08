@extends('layouts.master')

@section('title', 'Módulo de Permisos')

@section('content')

<style>
    .card-option {
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .option-card {
        position: relative;
        overflow: hidden;
        transition: all 0.28s ease;
        border: 1px solid transparent;
        min-height: 260px;
    }

    .option-card::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: 22px;
        width: 0;
        height: 5px;
        background: #d4b06a;
        border-radius: 20px;
        transform: translateX(-50%);
        transition: width 0.28s ease;
    }

    .option-card img {
        height: 95px;
        transition: transform 0.28s ease;
    }

    .option-card h4 {
        color: #1f3a56;
        transition: color 0.28s ease;
    }

    .card-option:hover .option-card {
        transform: translateY(-7px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.28);
        border-color: rgba(212, 176, 106, 0.65);
    }

    .card-option:hover .option-card img {
        transform: scale(1.12) translateY(-6px);
    }

    .card-option:hover .option-card h4 {
        color: #d4b06a;
    }

    .card-option:hover .option-card::after {
        width: 130px;
    }
</style>

<div class="container py-4">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-white">Módulo de Permisos</h2>
        <p class="text-light">
            Selecciona el tipo de gestión que deseas realizar
        </p>
    </div>

    <div class="row justify-content-center g-4">

        @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

            <!-- PERMISOS LABORALES ADMIN -->
            <div class="col-md-5">
                <a href="{{ route('permisos.index') }}" class="card-option">
                    <div class="glass-card option-card text-center p-4 h-100">

                        <img src="{{ asset('icons/permiso01.png') }}" class="mb-3">

                        <h4 class="fw-bold">Permisos laborales</h4>

                        <p class="text-muted">
                            Gestión de solicitudes para permisos de vacaciones, permisos personales, horas y otros.
                        </p>

                    </div>
                </a>
            </div>

            <!-- DÍA NO LABORAL ADMIN -->
            <div class="col-md-5">
                <a href="{{ route('compensatorios.solicitudes.index') }}" class="card-option">
                    <div class="glass-card option-card text-center p-4 h-100">

                        <img src="{{ asset('icons/permiso02.webp') }}" class="mb-3">

                        <h4 class="fw-bold">Día no laboral</h4>

                        <p class="text-muted">
                            Revisión y gestión de solicitudes para trabajar en días inhábiles.
                        </p>

                    </div>
                </a>
            </div>

        @elseif(auth()->user()->rol === 'jefe_departamento')

            <!-- PERMISOS LABORALES JEFE -->
            <div class="col-md-5">
                <a href="{{ route('permisos.mis') }}" class="card-option">
                    <div class="glass-card option-card text-center p-4 h-100">

                        <img src="{{ asset('icons/permisos.png') }}" class="mb-3">

                        <h4 class="fw-bold">Permisos laborales</h4>

                        <p class="text-muted">
                            Registrar solicitudes de permisos laborales para los empleados de tu departamento.
                        </p>

                    </div>
                </a>
            </div>

            <!-- DÍA NO LABORAL JEFE -->
            <div class="col-md-5">
                <a href="{{ route('compensatorios.solicitudes.mis') }}" class="card-option">
                    <div class="glass-card option-card text-center p-4 h-100">

                        <img src="{{ asset('icons/permisos.png') }}" class="mb-3">

                        <h4 class="fw-bold">Día no laboral</h4>

                        <p class="text-muted">
                            Registrar solicitudes por trabajo realizado en días inhábiles.
                        </p>

                    </div>
                </a>
            </div>

        @endif

                @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

    <!-- PERMISOS PRACTICANTES ADMIN -->
    <div class="col-md-5">
        <a href="{{ route('permisos-practicantes.index') }}" class="card-option">

            <div class="glass-card option-card text-center p-4 h-100">

                <img src="{{ asset('icons/permiso01.png') }}" class="mb-3">

                <h4 class="fw-bold">
                    Permisos de practicantes
                </h4>

                <p class="text-muted">
                    Gestión de solicitudes de permisos para practicantes y estudiantes en práctica profesional.
                </p>

            </div>

        </a>
    </div>

@elseif(auth()->user()->rol === 'jefe_departamento')

    <!-- PERMISOS PRACTICANTES JEFE -->
    <div class="col-md-5">
        <a href="{{ route('permisos-practicantes.mis') }}" class="card-option">

            <div class="glass-card option-card text-center p-4 h-100">

                <img src="{{ asset('icons/permisos.png') }}" class="mb-3">

                <h4 class="fw-bold">
                    Permisos de practicantes
                </h4>

                <p class="text-muted">
                    Registrar solicitudes de permisos para practicantes asignados a tu departamento.
                </p>

            </div>

        </a>
    </div>

@endif

    </div>



</div>

@endsection