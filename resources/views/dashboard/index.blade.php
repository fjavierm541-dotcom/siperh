@extends('layouts.configuracion')

@section('title', 'Control de vencimientos')

@section('config-content')

<style>

    .big-number {
        font-size: 3.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .riesgo-card {
        border-radius: 14px;
        transition: all .25s ease;
        background: white;
    }

    .riesgo-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,.12);
    }

</style>

<div class="glass-card overflow-hidden">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background: #27496d;">

        <div>
            <h4 class="text-white fw-bold mb-0">
                Control de vencimientos
            </h4>

            <small class="text-white-50">
                Resumen general del estado de vacaciones del personal.
            </small>
        </div>

    </div>

    <!-- CONTENIDO -->
    <div class="p-4">

        <div class="row g-4 text-center">

            <!-- ROJO -->
            <div class="col-md-4">

                <div class="card border-danger riesgo-card h-100 shadow-sm">

                    <div class="card-body py-4">

                        <h5 class="text-danger fw-bold mb-4">
                            🔴 Riesgo Alto
                        </h5>

                        <div class="big-number text-danger mb-3">
                            {{ $rojos }}
                        </div>

                        <p class="text-muted mb-0">
                            Vacaciones por vencer
                        </p>

                    </div>

                </div>

            </div>

            <!-- AMARILLO -->
            <div class="col-md-4">

                <div class="card border-warning riesgo-card h-100 shadow-sm">

                    <div class="card-body py-4">

                        <h5 class="text-warning fw-bold mb-4">
                            🟡 Riesgo Medio
                        </h5>

                        <div class="big-number text-warning mb-3">
                            {{ $amarillos }}
                        </div>

                        <p class="text-muted mb-0">
                            Próximas a vencer
                        </p>

                    </div>

                </div>

            </div>

            <!-- VERDE -->
            <div class="col-md-4">

                <div class="card border-success riesgo-card h-100 shadow-sm">

                    <div class="card-body py-4">

                        <h5 class="text-success fw-bold mb-4">
                            🟢 Bajo Riesgo
                        </h5>

                        <div class="big-number text-success mb-3">
                            {{ $verdes }}
                        </div>

                        <p class="text-muted mb-0">
                            Sin riesgo inmediato
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- BOTÓN -->
        <div class="text-center mt-5">

            <a href="{{ route('empleados.index') }}"
               class="btn btn-primary-custom px-4">

                Ver listado completo de empleados

            </a>

        </div>

    </div>

</div>

@endsection