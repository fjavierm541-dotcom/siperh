@extends('layouts.master')

@section('title', 'Notificaciones')

@section('content')



<style>
    .notificacion-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        transition: all .25s ease;
    }

    .notificacion-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(0,0,0,.12);
    }

    .notificacion-no-leida {
        border-left: 5px solid #d4b06a;
    }

    .notificacion-leida {
        opacity: .75;
    }

    .tipo-badge {
        background: #1f3a56;
        color: white;
        border-radius: 50px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
    }
</style>

<div class="glass-card overflow-hidden">

    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background:#27496d;">

        <div>
            <h4 class="text-white fw-bold mb-0">
                Notificaciones
            </h4>

            <small class="text-white-50">
                Historial de avisos generados por el sistema.
            </small>
        </div>

        <a href="{{ route('paginas.inicio') }}" class="btn btn-primary-custom">
            Volver al inicio
        </a>

    </div>

    <div class="p-4">

        @forelse($notificaciones as $notificacion)

            <div class="notificacion-card p-3 mb-3
                {{ $notificacion->leida ? 'notificacion-leida' : 'notificacion-no-leida' }}">

                <div class="d-flex justify-content-between align-items-start gap-3">

                    <div>
                        <h6 class="fw-bold mb-1">
                            {{ $notificacion->titulo }}
                        </h6>

                        <p class="text-muted mb-2">
                            {{ $notificacion->mensaje }}
                        </p>

                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <span class="tipo-badge">
                                {{ ucfirst($notificacion->tipo) }}
                            </span>

                            @if($notificacion->modulo)
                                <small class="text-muted">
                                    Módulo: {{ ucfirst($notificacion->modulo) }}
                                </small>
                            @endif

                            <small class="text-muted">
                                {{ $notificacion->created_at?->format('d/m/Y h:i A') }}
                            </small>
                        </div>
                    </div>

                    @if($notificacion->url)
                        <a href="{{ route('notificaciones.abrir', $notificacion->id) }}"
                        class="btn btn-sm btn-outline-dark">
                            Ver
                        </a>
                    @endif

                </div>

            </div>

        @empty

            <div class="text-center text-muted py-5">
                No hay notificaciones registradas.
            </div>

        @endforelse

        <div class="d-flex justify-content-end mt-3">
            {{ $notificaciones->links() }}
        </div>

    </div>

</div>

@endsection