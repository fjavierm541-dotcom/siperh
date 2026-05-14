@extends('layouts.master')

@section('title','Departamentos')

@section('content')

<div class="glass-card">

@if($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif



<style>

    .btn-group .btn {
    border-color: #2d4f73;
    color: #2d4f73;
}
.btn-group .btn:hover {
    background: #2d4f73;
    color: white;
}

    </Style>

    <!-- HEADER -->
    <div class="p-3 text-white"
    style="background:#2f4f6f;border-top-left-radius:18px;border-top-right-radius:18px;">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Listado de Departamentos
            </h5>

            <div>

                <a href="{{ route('departamentos.create') }}"
                    class="btn btn-primary-custom btn-sm">
                    Registrar Departamento
                </a>
                <a href="{{ route('paginas.inicio') }}"
                class="btn btn-secondary btn-sm">
                    Volver
                </a>

            </div>

        </div>

    </div>
    @if(session('error'))
    <div class="alert alert-danger m-3">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success m-3">
        {{ session('success') }}
    </div>
@endif


<!-- BUSCADOR Y FILTROS -->
<div class="p-3">

    <form id="formBusqueda" method="GET" action="{{ route('departamentos.index') }}" class="mb-3">

        <div class="row g-2 align-items-center">

            <div class="col-md-6">

                <input
                    type="text"
                    id="buscarInput"
                    name="buscar"
                    class="form-control"
                    placeholder="Buscar por nombre de depto. o código"
                    value="{{ request('buscar') }}">

            </div>

            <div class="col-md-2">

                <select name="estado"
                        class="form-select"
                        onchange="this.form.submit()">

                    <option value="activos" {{ request('estado', 'activos') == 'activos' ? 'selected' : '' }}>
                        Activos
                    </option>

                    <option value="inactivos" {{ request('estado') == 'inactivos' ? 'selected' : '' }}>
                        Inactivos
                    </option>

                    <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>
                        Todos
                    </option>

                </select>

            </div>

            <div class="col-md-2">

                <div class="dropdown w-100">

                    <button class="btn btn-primary-custom w-100 dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">

                        Imprimir

                    </button>

                    <ul class="dropdown-menu w-100">

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('departamentos.imprimir', ['estado' => 'activos']) }}"
                               target="_blank">
                                Departamentos activos
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('departamentos.imprimir', ['estado' => 'inactivos']) }}"
                               target="_blank">
                                Departamentos inactivos
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('departamentos.imprimir', ['estado' => 'todos']) }}"
                               target="_blank">
                                Todos los departamentos
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

            <div class="col-md-2">

                <a href="{{ route('departamentos.index') }}"
                   class="btn btn-secondary w-100">

                    Limpiar

                </a>

            </div>

        </div>

    </form>

</div>


    <!-- TABLA -->

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead style="background:#3a5a7c;color:white">

                <tr>

                    <th width="60">#</th>

                    <th width="120">Código</th>

                    <th>Departamento</th>

                    <th width="120">Estado</th>

                    <th width="260">Acciones</th>

                </tr>

            </thead>

            <tbody id="tablaDepartamentos">

                @forelse($departamentos as $i => $dep)

                <tr>

                    <td>
                        {{ $departamentos->firstItem() + $i }}
                    </td>

                    <td>
                        <strong>{{ $dep->codigo }}</strong>
                    </td>

                    <td>
                        {{ $dep->nombre }}
                    </td>

                    <td>

                        @if($dep->activo)

                        <span class="badge bg-success">
                            Activo
                        </span>

                        @else

                        <span class="badge bg-secondary">
                            Inactiv
                        </span>

                        @endif

                    </td>

                    <td>
                    <div class="btn-group" role="group">

                        <a href="{{ route('departamentos.show',$dep->id) }}"
                        class="btn btn-outline-dark btn-sm">
                            Ver
                        </a>

                        <a href="{{ route('departamentos.edit',$dep->id) }}"
                        class="btn btn-outline-warning btn-sm">
                            Editar
                        </a>

                        <form method="POST"
                        action="{{ route('departamentos.toggle',$dep->id) }}"
                        class="d-inline">

                            @csrf
                            @method('PATCH')

                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                onclick="confirmarCambioEstado(this)">
                            {{ $dep->activo ? 'Inactivar' : 'Activar' }}
                        </button>

                        </form>

                    </div>
                </td>

                </tr>

                @empty

                <tr>

                    <td colspan="5"
                    class="text-center text-muted">

                        No hay departamentos registrados

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <!-- PAGINACIÓN -->

    <div class="mt-3 d-flex justify-content-center">

    {{ $departamentos->links() }}

</div>

</div>





<!-- TABLA -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formBusqueda')
    const input = document.getElementById('buscarInput')

    let timer = null

    input.addEventListener('keyup', function(){

        clearTimeout(timer)

        timer = setTimeout(() => {

            form.submit()

        }, 600)

    })

})


</script>

<div class="modal fade" id="modalEstadoDepto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-warning">
                <h5 class="modal-title">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p id="mensajeModalEstado" class="mb-0"></p>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-warning"
                        id="btnConfirmarEstado">
                    Sí, continuar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    let formEstadoSeleccionado = null;

    function confirmarCambioEstado(boton) {

        formEstadoSeleccionado = boton.closest('form');

        const accion = boton.textContent.trim();

        const mensaje = accion === 'Inactivar'
            ? '¿Está seguro de inactivar este departamento? Solo podrá hacerlo si no tiene empleados asignados.'
            : '¿Está seguro de activar nuevamente este departamento?';

        document.getElementById('mensajeModalEstado').textContent = mensaje;

        const modal = new bootstrap.Modal(document.getElementById('modalEstadoDepto'));
        modal.show();
    }

    document.getElementById('btnConfirmarEstado').addEventListener('click', function () {
        if (formEstadoSeleccionado) {
            formEstadoSeleccionado.submit();
        }
    });
</script>

@endsection