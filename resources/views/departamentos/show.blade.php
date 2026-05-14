@extends('layouts.master')

@section('title','Departamento')

@section('content')

<div class="glass-card">

    <!-- HEADER -->
    <div class="p-3 text-white"
        style="background:#2f4f6f;border-top-left-radius:18px;border-top-right-radius:18px;">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h5 class="mb-0">
                Departamento {{ $departamento->codigo }}
            </h5>

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

    <!-- ALERTAS -->
    @if(session('success'))
        <div class="alert alert-success m-3">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger m-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- INFO -->
    <div class="p-3">

        <p>
            <strong>Nombre:</strong>
            {{ $departamento->nombre }}
        </p>

        <p>
            <strong>Jefe de departamento:</strong>

            @if($departamento->jefe_dni)

                {{ $departamento->jefe->primer_nombre ?? '' }}
                {{ $departamento->jefe->primer_apellido ?? '' }}

            @else

                <span class="text-muted">
                    No asignado
                </span>

            @endif
        </p>

        <hr>

        <p>
            <strong>Total empleados:</strong>
            {{ $departamento->empleadosFuncionales->count() }}
        </p>

    </div>

    <!-- TABLA EMPLEADOS -->
    <div class="table-responsive">

        <table class="table align-middle">

            <thead style="background:#3a5a7c;color:white">

                <tr>

                    <th>DNI</th>

                    <th>Nombre</th>

                    <th>Puesto</th>

                    <th width="140">Acción</th>

                </tr>

            </thead>

            <tbody>

                @forelse($departamento->empleadosFuncionales as $emp)

                    <tr>

                        <td>
                            {{ $emp->DNI }}
                        </td>

                        <td>
                            {{ $emp->primer_nombre }}
                            {{ $emp->segundo_nombre }}

                            {{ $emp->primer_apellido }}
                            {{ $emp->segundo_apellido }}
                        </td>

                        <td>
                            {{ $emp->puesto }}
                        </td>

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

                        <td colspan="4"
                            class="text-center text-muted">

                            Este departamento aún no tiene empleados asignados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

<!-- MODAL -->
<div class="modal fade"
     id="modalRetirarEmpleado"
     tabindex="-1">

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

                <form id="formRetirarEmpleado"
                      method="POST">

                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="btn btn-warning">

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