@extends('layouts.master')

@section('title', 'Permisos de Practicantes')

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

```
<div class="glass-card">

    <div class="card-header-custom p-4 d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Permisos de Practicantes
        </h4>

        <div class="d-flex gap-2">

            <a href="{{ route('permisos-practicantes.create') }}"
               class="btn btn-gold">

                + Nuevo Permiso

            </a>

            <a href="{{ route('permisos.menu') }}"
               class="btn btn-outline-light">

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
      id="formBusqueda"
      class="mb-4">

    <div class="row g-3">

        <div class="col-md-6">

            <label class="form-label">
                Buscar
            </label>

            <input type="text"
       id="buscar"
       name="buscar"
       class="form-control"
       placeholder="Practicante, DNI o tipo..."
       value="{{ request('buscar') }}">

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Desde
            </label>

            <input type="date"
                   name="fecha_desde"
                   class="form-control"
                   value="{{ request('fecha_desde') }}">

        </div>

        <div class="col-md-2">

            <label class="form-label">
                Hasta
            </label>

            <input type="date"
                   name="fecha_hasta"
                   class="form-control"
                   value="{{ request('fecha_hasta') }}">

        </div>

        

        <div class="col-md-1 d-flex align-items-end">

            <a href="{{ route('permisos-practicantes.mis') }}"
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

                        <th>Practicante</th>
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
                            {{ $permiso->practicante->nombre_completo ?? '-' }}
                        </td>

                        <td>
                            <span class="badge bg-info text-dark">
                                {{ ucfirst(str_replace('_',' ', $permiso->modalidad)) }}
                            </span>
                        </td>

                        <td>
                            {{ $permiso->tipo->nombre ?? '-' }}
                        </td>

                        <td>
                            {{ $permiso->fecha_inicio ? $permiso->fecha_inicio->format('d-m-Y') : '-' }}
                        </td>

                        <td>
                            {{ $permiso->fecha_fin ? $permiso->fecha_fin->format('d-m-Y') : '-' }}
                        </td>

                        <td>

                            @if($permiso->modalidad == 'horas')

                                {{ $permiso->horas }}

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            @if($permiso->documento)

                                <a href="{{ asset('storage/'.$permiso->documento) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">

                                    👁

                                </a>

                            @else

                                —

                            @endif

                        </td>

                        <td>

    @if($permiso->estado->nombre == 'Pendiente')

        <span class="badge bg-warning text-dark">
            Pendiente
        </span>

    @elseif($permiso->estado->nombre == 'Aprobado')

        <span class="badge bg-success">
            Aprobado
        </span>

    @elseif($permiso->estado->nombre == 'Rechazado')

        <div>

            <span class="badge bg-danger">
                Rechazado
            </span>

            @if(!empty($permiso->motivo_rechazo))

                <div class="mt-1">

                    <a href="#"
                       class="text-secondary text-decoration-underline small"
                       onclick="verMotivoRechazo(`{{ e($permiso->motivo_rechazo) }}`); return false;">

                        Ver motivo

                    </a>

                </div>

            @endif

        </div>

    @elseif($permiso->estado->nombre == 'Cancelado')

        <span class="badge bg-secondary">
            Cancelado
        </span>

    @endif

</td>

                        <td>

                            @if($permiso->estado->nombre == 'Pendiente')
                            

                                <form method="POST"
                                      action="{{ route('permisos-practicantes.cancelar', $permiso->id) }}">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">

                                        Cancelar

                                    </button>

                                </form>

                            @endif

                            

                        </td>

                        <td>

                         <a href="{{ route('permisos-practicantes.imprimir', $permiso->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">

                                    🖨️

                                </a>
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9">

                            No hay permisos registrados.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $permisos->links() }}
        </div>

    </div>

</div>


</div>

<!-- MODAL MOTIVO RECHAZO -->

<div class="modal fade"
     id="modalMotivoRechazo"
     tabindex="-1">

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
                   style="
                        white-space: pre-wrap;
                        word-break: break-word;
                        overflow-wrap: break-word;
                   ">
                </p>

            </div>

        </div>

    </div>

</div>

<script>

function verMotivoRechazo(motivo)
{
    document.getElementById(
        'textoMotivoRechazo'
    ).textContent = motivo;

    new bootstrap.Modal(
        document.getElementById(
            'modalMotivoRechazo'
        )
    ).show();
}

</script>

<script>

let temporizador;

document.getElementById('buscar')
    .addEventListener('input', function() {

        clearTimeout(temporizador);

        temporizador = setTimeout(function() {

            document.getElementById('formBusqueda')
                .submit();

        }, 400);

    });

</script>

<script>

document.querySelector('[name="fecha_desde"]')
    .addEventListener('change', function() {

        document.getElementById('formBusqueda')
            .submit();

    });

document.querySelector('[name="fecha_hasta"]')
    .addEventListener('change', function() {

        document.getElementById('formBusqueda')
            .submit();

    });

</script>

@endsection
