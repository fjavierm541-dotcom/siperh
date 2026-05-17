@extends('layouts.master')

@section('title', 'Mis Solicitudes')

@section('content')

<style>
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

    .btn-blue {
        background-color: #1f3a56;
        border: none;
        color: white;
        font-weight: 600;
    }

    .btn-blue:hover {
        background-color: #162a40;
        color: white;
    }

    table th {
        background-color: #2d4f73 !important;
        color: white;
    }
</style>

<div class="glass-card p-0 overflow-hidden">

    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background:#2d4f73; color:white;">

        <h5 class="mb-0">
            Mis solicitudes de trabajo en día no laboral
        </h5>

        <div class="d-flex gap-2">

            <a href="{{ route('compensatorios.solicitudes.create') }}"
               class="btn btn-gold btn-sm">

                + Nueva Solicitud

            </a>

            <a href="{{ route('permisos.menu') }}"
               class="btn btn-outline-light btn-sm">

                Volver

            </a>

        </div>
    </div>

    <div class="p-4">

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <form method="GET"
              action="{{ route('compensatorios.solicitudes.mis') }}"
              class="mb-3">

            <div class="row g-2 align-items-end">

                <div class="col-md-5">

                    <label class="form-label">Buscar</label>

                    <input type="text"
                           name="buscar"
                           class="form-control"
                           placeholder="Estado o descripción"
                           value="{{ request('buscar') }}">

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

                <div class="col-md-3 d-flex gap-2">

                    <button class="btn btn-blue w-100">
                        Buscar
                    </button>

                    <a href="{{ route('compensatorios.solicitudes.mis') }}"
                       class="btn btn-outline-secondary w-100">

                        Limpiar

                    </a>

                </div>

            </div>

        </form>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Departamento</th>
                        <th>Fecha</th>
                        <th>Empleados</th>
                        <th>Estado</th>
                        <th>Doc.</th>
                        <th>Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($solicitudes as $sol)

                        <tr>

                            <td>{{ $sol->id }}</td>

                            <td>
                                {{ $sol->departamento->nombre ?? '—' }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($sol->created_at)->format('d-m-Y') }}
                            </td>

                            <td>
                                {{ $sol->empleados->count() }}
                            </td>

                            <td>

                                @if($sol->estado == 'pendiente')

                                    <span class="badge bg-warning text-dark">
                                        Pendiente
                                    </span>

                                @elseif($sol->estado == 'aprobado')

                                    <span class="badge bg-success">
                                        Aprobado
                                    </span>

                                @elseif($sol->estado == 'cancelado')

                                    <span class="badge bg-secondary">
                                        Cancelado
                                    </span>

                                @else

                                    <span class="badge bg-danger">
                                        Rechazado
                                    </span>

                                @endif

                            </td>

                            <td>

                                @if($sol->documento_path)

                                    <a href="{{ asset('storage/' . $sol->documento_path) }}"
                                       target="_blank"
                                       class="btn btn-outline-primary btn-sm">

                                        🖨️

                                    </a>

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a href="{{ route('compensatorios.solicitudes.show', $sol->id) }}"
                                       class="btn btn-blue btn-sm">

                                        Ver

                                    </a>

                                    @if($sol->estado == 'pendiente')

                                        <form method="POST"
                                              action="{{ route('compensatorios.solicitudes.cancelar', $sol->id) }}"
                                              onsubmit="return confirm('¿Cancelar solicitud?')">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm">

                                                Cancelar

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="text-center text-muted">

                                No hay solicitudes registradas.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="d-flex justify-content-end mt-3">
            {{ $solicitudes->links() }}
        </div>

    </div>

</div>

@endsection