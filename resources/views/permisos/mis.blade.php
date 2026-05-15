@extends('layouts.master')

@section('title', 'Mis Permisos')

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
    }

    table th {
        background-color: #2d4f73 !important;
        color: white;
    }
</style>

<div class="container py-5">

    <div class="glass-card">

        <div class="card-header-custom p-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Mis Permisos Enviados</h4>

            <div class="d-flex gap-2">

                <a href="{{ route('permisos.create') }}" class="btn btn-gold">
                    + Nuevo Permiso
                </a>

                <a href="{{ route('permisos.menu') }}" class="btn btn-outline-light">
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

            @if(session('permiso_imprimir'))
                <script>
                    window.addEventListener('load', function () {
                        setTimeout(function () {
                            window.open(
                                "{{ route('permisos.imprimir', session('permiso_imprimir')) }}",
                                "_blank",
                                "noopener,noreferrer"
                            );
                        }, 500);
                    });
                </script>
            @endif

            <form id="formBusquedaPermisos"
                  method="GET"
                  action="{{ route('permisos.mis') }}"
                  class="mb-3">

                <div class="row g-2 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label">Buscar</label>
                        <input type="text"
                               id="buscarPermisoInput"
                               name="buscar"
                               class="form-control"
                               placeholder="Empleado, DNI, modalidad o tipo"
                               value="{{ request('buscar') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date"
                               id="fechaDesdeInput"
                               name="fecha_desde"
                               class="form-control"
                               value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date"
                               id="fechaHastaInput"
                               name="fecha_hasta"
                               class="form-control"
                               value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-primary-custom w-100">
                            Buscar
                        </button>

                        <a href="{{ route('permisos.mis') }}"
                           class="btn btn-outline-secondary w-100">
                            Limpiar
                        </a>
                    </div>

                </div>

            </form>

            <div class="table-responsive">

                <table class="table table-hover align-middle text-center">

                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Modalidad</th>
                            <th>Tipo</th>
                            <th>Fecha inicial</th>
                            <th>Fecha final</th>
                            <th>Horas</th>
                            <th>Doc.</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($permisos as $permiso)

                            <tr>

                                <td class="text-start">
                                    {{ $permiso->empleado->primer_nombre ?? '' }}
                                    {{ $permiso->empleado->segundo_nombre ?? '' }}
                                    {{ $permiso->empleado->primer_apellido ?? '' }}
                                    {{ $permiso->empleado->segundo_apellido ?? '' }}
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ ucfirst(str_replace('_',' ', $permiso->modalidad)) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $permiso->tipo->nombre ?? '' }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($permiso->fecha_inicio)->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ $permiso->fecha_fin ? \Carbon\Carbon::parse($permiso->fecha_fin)->format('d-m-Y') : '-' }}
                                </td>

                                <td>
                                    @if($permiso->modalidad == 'horas')

                                        @php
                                            $horasEnteras = floor($permiso->horas);
                                            $minutos = round(($permiso->horas - $horasEnteras) * 60);
                                            $texto = '';

                                            if ($horasEnteras > 0) {
                                                $texto .= $horasEnteras . 'h ';
                                            }

                                            if ($minutos > 0) {
                                                $texto .= $minutos . 'min';
                                            }

                                            echo trim($texto);
                                        @endphp

                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if($permiso->documento)

                                        <a href="{{ asset('storage/' . $permiso->documento) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            👁
                                        </a>

                                    @else

                                        <span class="text-muted">—</span>

                                    @endif
                                </td>

                                <td>
    @if($permiso->estado->nombre == 'Pendiente')

        <span class="badge bg-warning text-dark">Pendiente</span>

    @elseif($permiso->estado->nombre == 'Aprobado')

        <span class="badge bg-success">Aprobado</span>

    @elseif($permiso->estado->nombre == 'Rechazado')

        <span class="badge bg-danger">Rechazado</span>

        @if($permiso->motivo_rechazo)
            <div class="mt-1">
                <button type="button"
                        class="btn btn-link btn-sm p-0 text-secondary"
                        onclick="verMotivoRechazo(`{{ addslashes($permiso->motivo_rechazo) }}`)">
                    Ver motivo
                </button>
            </div>
        @endif

    @elseif($permiso->estado->nombre == 'Cancelado')

        <span class="badge bg-secondary">Cancelado</span>

    @else

        <span class="badge bg-dark">{{ $permiso->estado->nombre }}</span>

    @endif
</td>

                                <td>

                                @if($permiso->estado->nombre == 'Pendiente')

                                    <form method="POST"
                                        action="{{ route('permisos.cancelar', $permiso->id) }}"
                                        onsubmit="return confirm('¿Seguro que deseas cancelar este permiso?');">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger">
                                            Cancelar
                                        </button>

                                    </form>

                                @else

                                    <span class="text-muted">—</span>

                                @endif

                            </td>

                                <td>
                                    <a href="{{ route('permisos.imprimir', $permiso->id) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        🖨️
                                    </a>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="10" class="text-muted">
                                    No hay permisos registrados.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $permisos->links() }}
            </div>

        </div>

    </div>

</div>

<!-- MODAL VER MOTIVO -->
<div class="modal fade" id="modalMotivoRechazo" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-danger text-white">

                <h5 class="modal-title">
                    Motivo del rechazo
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p id="textoMotivoRechazo"
                   class="mb-0"
                   style="white-space: pre-wrap; word-break: break-word;">
                </p>

            </div>

        </div>

    </div>

</div>

<script>
function verMotivoRechazo(motivo)
{
    document.getElementById('textoMotivoRechazo').textContent = motivo;

    const modal = new bootstrap.Modal(
        document.getElementById('modalMotivoRechazo')
    );

    modal.show();
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formBusquedaPermisos');
    const buscar = document.getElementById('buscarPermisoInput');
    const desde = document.getElementById('fechaDesdeInput');
    const hasta = document.getElementById('fechaHastaInput');

    let timer = null;

    function buscarAutomatico() {
        clearTimeout(timer);

        timer = setTimeout(() => {
            form.submit();
        }, 900);
    }

    buscar.addEventListener('keyup', buscarAutomatico);

    desde.addEventListener('change', function () {
        form.submit();
    });

    hasta.addEventListener('change', function () {
        form.submit();
    });

});
</script>

@endsection