@extends('layouts.master')

@section('title', 'Bitácora del Sistema')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #1f3a56, #2d4f73);
        min-height: 100vh;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    }

    .card-header-custom {
        background-color: #274769;
        color: white;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
    }

    .btn-gold {
        background-color: #d4b06a;
        border: none;
        color: #1f3a56;
        font-weight: 600;
    }

    .btn-gold:hover {
        background-color: #c39a4f;
        color: #1f3a56;
    }

    .btn-primary-custom {
        background-color: #1f3a56;
        border: none;
        color: white;
    }

    .btn-primary-custom:hover {
        background-color: #162a40;
        color: white;
    }

    table th {
        background-color: #2d4f73 !important;
        color: white;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
    }

    .badge-modulo {
        background-color: #d4b06a;
        color: #1f3a56;
        font-weight: 600;
    }

    .small-muted {
        font-size: 12px;
        color: #6c757d;
    }

    .descripcion-cell {
        max-width: 360px;
        white-space: normal;
    }
</style>

<div class="container py-5">

    <div class="glass-card">

        <div class="card-header-custom p-4 d-flex justify-content-between align-items-center">

            <div>
                <h4 class="mb-1">Bitácora del Sistema</h4>
                <small>Registro de acciones importantes realizadas en SIPERH</small>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('bitacora.imprimir', request()->query()) }}"
                   target="_blank"
                   class="btn btn-gold">
                    Imprimir / PDF
                </a>

                <a href="{{ route('paginas.inicio') }}"
                   class="btn btn-outline-light">
                    Volver
                </a>
            </div>

        </div>

        <div class="p-4">

            <form method="GET" action="{{ route('bitacora.index') }}" class="mb-3">

                <div class="row g-2 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label">Buscar</label>
                        <input type="text"
                               name="buscar"
                               class="form-control"
                               placeholder="Usuario, acción, módulo, descripción o IP"
                               value="{{ request('buscar') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Módulo</label>
                        <select name="modulo" class="form-select">
                            <option value="">Todos los módulos</option>
                            <option value="usuarios" {{ request('modulo') == 'usuarios' ? 'selected' : '' }}>Usuarios</option>
                            <option value="empleados" {{ request('modulo') == 'empleados' ? 'selected' : '' }}>Empleados</option>
                            <option value="permisos" {{ request('modulo') == 'permisos' ? 'selected' : '' }}>Permisos</option>
                            <option value="vacaciones" {{ request('modulo') == 'vacaciones' ? 'selected' : '' }}>Vacaciones</option>
                            <option value="compensatorios" {{ request('modulo') == 'compensatorios' ? 'selected' : '' }}>Compensatorios</option>
                            <option value="departamentos" {{ request('modulo') == 'departamentos' ? 'selected' : '' }}>Departamentos</option>
                            <option value="correcciones" {{ request('modulo') == 'correcciones' ? 'selected' : '' }}>Correcciones</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date"
                               name="fecha_desde"
                               class="form-control"
                               value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date"
                               name="fecha_hasta"
                               class="form-control"
                               value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                        <button class="btn btn-primary-custom px-4">
                            Buscar
                        </button>

                        <a href="{{ route('bitacora.index') }}" class="btn btn-outline-secondary px-4">
                            Limpiar
                        </a>
                    </div>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle text-center">

                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acción</th>
                            <th>Módulo</th>
                            <th>Descripción</th>
                            <th>IP</th>
                            <th>Referencia</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($bitacoras as $item)

                            <tr>
                                <td>
                                    <strong>{{ $item->created_at?->format('d/m/Y') }}</strong>
                                    <div class="small-muted">
                                        {{ $item->created_at?->format('h:i A') }}
                                    </div>
                                </td>

                                <td class="text-start">
                                    {{ $item->usuario_nombre ?? 'Sistema' }}
                                    <div class="small-muted">
                                        {{ $item->empleado_dni ?? 'Sin DNI' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($item->rol_usuario ?? 'sistema') }}
                                    </span>
                                </td>

                                <td>
                                    {{ str_replace('_', ' ', ucfirst($item->accion)) }}
                                    <div class="small-muted">
                                        {{ $item->metodo }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge badge-modulo">
                                        {{ ucfirst($item->modulo) }}
                                    </span>
                                </td>

                                <td class="descripcion-cell text-start">
                                    {{ $item->descripcion }}
                                </td>

                                <td>
                                    {{ $item->ip_equipo ?? '-' }}
                                </td>

                                <td>
                                    @if($item->referencia_tipo || $item->referencia_id)
                                        <span class="small-muted">
                                            {{ $item->referencia_tipo ?? '-' }}
                                            #{{ $item->referencia_id ?? '-' }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-muted py-4">
                                    No se encontraron registros en la bitácora.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $bitacoras->links() }}
            </div>

        </div>

    </div>

</div>

@endsection