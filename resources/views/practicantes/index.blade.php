@extends('layouts.configuracion')

@section('title', 'Practicantes')

@section('config-content')

<div class="glass-card overflow-hidden">

    <div class="p-4 text-white d-flex justify-content-between align-items-center"
         style="background:#27496d;">

        <div>
            <h4 class="fw-bold mb-1">Practicantes</h4>
            <small>
                Administración de practicantes registrados en SIPERH.
            </small>
        </div>

        <div class="d-flex gap-2">

    <div class="dropdown">

        <button class="btn dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                style="background:#1f3a56;color:white;font-weight:600;">

            Imprimir practicantes

        </button>

        <ul class="dropdown-menu">

            <li>
                <a class="dropdown-item"
                   href="{{ route('practicantes.imprimir', 'activos') }}"
                   target="_blank">

                    Practicantes activos

                </a>
            </li>

            <li>
                <a class="dropdown-item"
                   href="{{ route('practicantes.imprimir', 'inactivos') }}"
                   target="_blank">

                    Practicantes inactivos

                </a>
            </li>

            <li>
                <a class="dropdown-item"
                   href="{{ route('practicantes.imprimir', 'todos') }}"
                   target="_blank">

                    Todos los practicantes

                </a>
            </li>

        </ul>

    </div>

    <a href="{{ route('practicantes.create') }}"
       class="btn"
       style="background:#d4b06a;color:#1f3a56;font-weight:600;">

        Nuevo practicante

    </a>

</div>

    </div>

    <div class="p-4">

        @if(session('success'))

            <div class="alert alert-success">
                {{ session('success') }}
            </div>

        @endif

        <form method="GET" class="mb-4">

            <div class="row g-3">

                <div class="col-md-10">

                    <label class="form-label">
                        Buscar
                    </label>

                    <input type="text"
                        id="buscar"
                        name="buscar"
                        class="form-control"
                        placeholder="Nombre, DNI o institución..."
                        value="{{ request('buscar') }}">

                </div>

                <div class="col-md-2 d-flex align-items-end">

                    <button class="btn btn-primary-custom w-100">
                        Buscar
                    </button>

                </div>

            </div>

        </form>

        <script>

let temporizador;

document.getElementById('buscar')
    .addEventListener('keyup', function() {

        clearTimeout(temporizador);

        temporizador = setTimeout(() => {

            this.form.submit();

        }, 500);

    });

</script>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead style="background:#27496d;color:white;">

                    <tr>
                        <th width="60">No.</th>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Institución</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <th width="120">Acciones</th>
                    </tr>

                </thead>

                <tbody>

                @forelse($practicantes as $practicante)

                    <tr>

                        <td>
                            {{ $practicantes->firstItem() + $loop->index }}
                        </td>

                        <td>
                            {{ $practicante->nombre_completo }}
                        </td>

                        <td>
                            {{ $practicante->dni_practicante ?: '-' }}
                        </td>

                        <td>
                            {{ $practicante->institucion }}
                        </td>

                        <td>
                            {{ $practicante->departamento->nombre ?? '-' }}
                        </td>

                        <td>

                            @if($practicante->activo)

                                <span class="badge bg-success">
                                    Activo
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Inactivo
                                </span>

                            @endif

                        </td>

                       <td>

                            <div class="d-flex gap-1">

                                <a href="{{ route('practicantes.edit', $practicante) }}"
                                class="btn btn-sm btn-outline-primary">

                                    Editar

                                </a>

                                <button
                                    type="button"
                                    class="btn btn-sm {{ $practicante->activo ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    onclick="abrirModalEstadoPracticante(
                                        '{{ route('practicantes.toggle', $practicante) }}',
                                        '{{ $practicante->nombre_completo }}',
                                        {{ $practicante->activo ? 'true' : 'false' }}
                                    )">

                                    {{ $practicante->activo ? 'Inactivar' : 'Activar' }}

                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7"
                            class="text-center py-4 text-muted">

                            Sin estudiantes practicantes.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">

            {{ $practicantes->links() }}

        </div>

    </div>

</div>

<!-- Modal cambio estado practicante -->
<div class="modal fade"
     id="modalEstadoPracticante"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-header text-white"
                 style="background:#27496d;">

                <h4 class="modal-title fw-bold text-center w-100"
                    id="tituloEstadoPracticante">

                    ¿Está seguro?

                </h4>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body text-center py-4">

                <h5 id="nombrePracticanteModal"
                    class="fw-bold mb-4">
                </h5>

                <p class="text-muted mb-0">
                    Verifique cuidadosamente esta acción antes de continuar.
                </p>

            </div>

            <div class="modal-footer justify-content-center">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Cancelar

                </button>

                <form id="formEstadoPracticante"
                      method="POST">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="btn"
                            id="btnConfirmarEstado">

                        Confirmar

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

function abrirModalEstadoPracticante(
    action,
    nombre,
    activo
){

    document.getElementById('formEstadoPracticante')
        .action = action;

    document.getElementById('nombrePracticanteModal')
        .innerText = nombre;

    const titulo =
        document.getElementById('tituloEstadoPracticante');

    const boton =
        document.getElementById('btnConfirmarEstado');

    if(activo){

        titulo.innerText =
            '¿Está seguro de inactivar este practicante?';

        boton.className =
            'btn btn-danger';

    }else{

        titulo.innerText =
            '¿Está seguro de activar este practicante?';

        boton.className =
            'btn btn-success';

    }

    const modal = new bootstrap.Modal(
        document.getElementById(
            'modalEstadoPracticante'
        )
    );

    modal.show();
}

</script>

@endsection