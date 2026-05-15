@extends('layouts.master')

@section('title', 'Usuarios')

@section('content')

<style>
    .usuarios-table {
        width: 100%;
        table-layout: auto;
    }

    .usuarios-table th,
    .usuarios-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .acciones-usuarios {
        width: 1%;
        white-space: nowrap;
    }

    .acciones-usuarios .btn-group .btn {
        border-radius: 0 !important;
        margin: 0 !important;
    }

    .acciones-usuarios .btn-group .btn:first-child {
        border-top-left-radius: 6px !important;
        border-bottom-left-radius: 6px !important;
    }

    .acciones-usuarios .btn-group .btn:last-child {
        border-top-right-radius: 6px !important;
        border-bottom-right-radius: 6px !important;
    }

    .acciones-usuarios .btn-group .btn + .btn {
        margin-left: -1px !important;
    }
</style>

<div class="glass-card overflow-hidden">

    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background: #27496d;">

        <h4 class="text-white fw-bold mb-0">
            Usuarios del Sistema
        </h4>

        <a href="{{ route('usuarios.create') }}"
           class="btn btn-primary-custom">
            Registrar Usuario
        </a>

    </div>

    <div class="p-4">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0 usuarios-table">

                <thead style="background:#355c85; color:white;">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Usuario</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="acciones-usuarios">Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($usuarios as $index => $usuario)

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>{{ $usuario->username }}</td>

                            <td>{{ $usuario->name }}</td>

                            <td>
                                @if($usuario->rol === 'superadmin')
                                    <span class="badge bg-dark">Superadmin</span>
                                @elseif($usuario->rol === 'rrhh')
                                    <span class="badge bg-primary">RRHH</span>
                                @else
                                    <span class="badge bg-secondary">Jefe Departamento</span>
                                @endif
                            </td>

                            <td>
                                @if($usuario->activo)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>

                            <td class="acciones-usuarios">

                                <div class="btn-group btn-group-sm" role="group">

                                    <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                       class="btn btn-outline-dark">
                                        Editar
                                    </a>

                                    <button type="button"
                                            class="btn btn-outline-warning"
                                            onclick="document.getElementById('reset-form-{{ $usuario->id }}').submit();">
                                        Restablecer
                                    </button>

                                    <button type="button"
                                            class="btn {{ $usuario->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                            onclick="document.getElementById('toggle-form-{{ $usuario->id }}').submit();">
                                        {{ $usuario->activo ? 'Desactivar' : 'Activar' }}
                                    </button>

                                </div>

                                <form id="reset-form-{{ $usuario->id }}"
                                      method="POST"
                                      action="{{ route('usuarios.resetPassword', $usuario->id) }}"
                                      class="d-none">
                                    @csrf
                                    @method('PATCH')
                                </form>

                                <form id="toggle-form-{{ $usuario->id }}"
                                      method="POST"
                                      action="{{ route('usuarios.toggle', $usuario->id) }}"
                                      class="d-none">
                                    @csrf
                                    @method('PATCH')
                                </form>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay usuarios registrados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection