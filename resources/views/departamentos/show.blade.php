@extends('layouts.master')

@section('title','Departamento')

@section('content')

<div class="glass-card overflow-hidden">

    <!-- HEADER -->
    <div class="px-4 py-3 text-white" style="background:#27496d;">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <h4 class="mb-0 fw-bold">
                    {{ $departamento->nombre }}
                </h4>

                <small class="text-white-50">
                    Código: {{ $departamento->codigo }}
                </small>
            </div>
 
            <div class="d-flex flex-wrap gap-2">

                <a href="{{ route('departamentos.imprimirEmpleados', $departamento->id) }}"
                   target="_blank"
                   class="btn btn-primary-custom btn-sm">
                    Imprimir empleados
                </a>

                <a href="{{ route('departamentos.jefe',$departamento->id) }}"
                   class="btn btn-primary-custom btn-sm">
                    Cambiar jefe
                </a>

                <a href="{{ route('departamentos.asignar',$departamento->id) }}"
                   class="btn btn-primary-custom btn-sm">
                    Agregar empleados
                </a>

                <a href="{{ route('departamentos.index') }}"
                   class="btn btn-secondary btn-sm">
                    Volver
                </a>

            </div>

        </div>

    </div>

    @if(session('success'))
        <div class="alert alert-success m-4 mb-0">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger m-4 mb-0">
            {{ session('error') }}
        </div>
    @endif

    <!-- INFO -->
    <div class="p-4">

        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm h-100">
                    <small class="text-muted">Departamento</small>
                    <div class="fw-bold">
                        {{ $departamento->nombre }}
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm h-100">
                    <small class="text-muted">Jefe de departamento</small>
                    <div class="fw-bold">
                        @if($departamento->jefe_dni)
                            {{ $departamento->jefe->primer_nombre ?? '' }}
                            {{ $departamento->jefe->primer_apellido ?? '' }}
                        @else
                            <span class="text-muted">No asignado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 bg-white rounded shadow-sm h-100">
                    <small class="text-muted">Total empleados</small>
                    <div class="fw-bold">
                        {{ $departamento->empleadosFuncionales->count() }}
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLA -->
        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead style="background:#355c85;color:white;">
                    <tr>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Puesto Actual</th>
                        <th width="140">Acción</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($departamento->empleadosFuncionales as $emp)

                        <tr>

                            <td>{{ $emp->DNI }}</td>

                            <td>
                                {{ $emp->primer_nombre }}
                                {{ $emp->segundo_nombre }}
                                {{ $emp->primer_apellido }}
                                {{ $emp->segundo_apellido }}
                            </td>

                            <td>{{ $emp->puesto_funcional ?: $emp->puesto }}</td>

                            <td>
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="abrirModalRetirar(
                                            '{{ route('departamentos.retirarEmpleado', [$departamento->id, $emp->DNI]) }}',
                                            '{{ $emp->primer_nombre }} {{ $emp->primer_apellido }}'
                                        )">
                                    Retirar
                                </button>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Este departamento aún no tiene empleados asignados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="modalRetirarEmpleado" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-warning">

                <h5 class="modal-title">
                    Confirmar retiro
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p class="mb-0">
                    ¿Está seguro de retirar a
                    <strong id="nombreEmpleadoRetirar"></strong>
                    de este departamento?
                </p>

                <small class="text-muted">
                    El empleado quedará sin departamento funcional asignado.
                </small>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <form id="formRetirarEmpleado" method="POST">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-warning">
                        Sí, retirar
                    </button>
                </form>

            </div>

        </div>

    </div>

</div>

<script>
function abrirModalRetirar(url, nombre)
{
    document.getElementById('formRetirarEmpleado').action = url;
    document.getElementById('nombreEmpleadoRetirar').textContent = nombre;

    const modal = new bootstrap.Modal(
        document.getElementById('modalRetirarEmpleado')
    );

    modal.show();
}
</script>

@endsection