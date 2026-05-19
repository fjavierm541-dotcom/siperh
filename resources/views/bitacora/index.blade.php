@extends('layouts.configuracion')

@section('title', 'Bitácora del Sistema')

@section('config-content')

<style>
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

    .bitacora-table {
        font-size: 14px;
    }

    .bitacora-table th {
        background-color: #2d4f73 !important;
        color: white;
        font-size: 14px;
        padding: 12px 10px;
        vertical-align: middle;
    }

    .bitacora-table td {
        padding: 14px 10px;
        vertical-align: middle;
    }

    .bitacora-table .texto-principal {
        font-size: 14px;
        line-height: 1.35;
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
        max-width: 330px;
        white-space: normal;
        line-height: 1.35;
    }

    .fecha-cell {
        white-space: nowrap;
        font-weight: 700;
    }
</style>

<div class="glass-card overflow-hidden">

    <div class="card-header-custom p-4 d-flex justify-content-between align-items-center">

        <div>
            <h4 class="mb-1 fw-bold">Bitácora del Sistema</h4>
            <small>Registro de acciones importantes realizadas en SIPERH</small>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('bitacora.imprimir', request()->query()) }}"
               target="_blank"
               class="btn btn-gold">
                Imprimir / PDF
            </a>
        </div>

    </div>

    <div class="p-4">

        <form method="GET" action="{{ route('bitacora.index') }}" class="mb-4">

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

            <table class="table table-hover align-middle text-center bitacora-table">

                <thead>
                    <tr>
                        <th>Fecha</th>
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
                            <td class="fecha-cell">
                                {{ $item->created_at?->format('d/m/Y') }}
                            </td>

                            <td class="text-start">
                                <div class="texto-principal">
                                    {{ $item->usuario_nombre ?? 'Sistema' }}
                                </div>

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
                                <div class="texto-principal">
                                    {{ str_replace('_', ' ', ucfirst($item->accion)) }}
                                </div>

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
                                <span class="texto-principal">
                                    {{ $item->ip_equipo ?? '-' }}
                                </span>
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

@endsection