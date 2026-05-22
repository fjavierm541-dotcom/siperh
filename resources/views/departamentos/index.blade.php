@extends('layouts.master')

@section('title','Departamentos')

@section('content')

<div class="glass-card overflow-hidden">

@if($errors->any())

    <div class="alert alert-danger m-3">

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<style>

    .table thead th {

        background: linear-gradient(90deg,#24476b,#31597f) !important;
        color: white;
        border: none;
        padding: 14px 12px;
        font-size: 13px;
        letter-spacing: .3px;
    }

    .table tbody tr {

        transition: all .18s ease;
    }

    .table tbody tr:hover {

        background: #f3f7fb;
    }

    .table td {

        vertical-align: middle;
        padding: 14px 12px;
    }

    .badge-estado {

        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-activo {

        background: #198754;
        color: white;
    }

    .badge-inactivo {

        background: #6c757d;
        color: white;
    }

    .codigo-depto {

        font-weight: bold;
        color: #1f3a56;
        font-size: 14px;
    }

    .cantidad-empleados {

        background: #e8f1fb;
        color: #1f3a56;
        border-radius: 20px;
        padding: 4px 10px;
        font-weight: bold;
        display: inline-block;
        min-width: 40px;
        text-align: center;
    }

    .btn-group .btn {

        border-color: #2d4f73;
        color: #2d4f73;
    }

    .btn-group .btn:hover {

        background: #2d4f73;
        color: white;
    }

</style>

<!-- HEADER -->
<div class="p-4 text-white"
     style="background:linear-gradient(90deg,#1f3a56,#2d4f73);">

    <div class="d-flex justify-content-between align-items-center">

        <h4 class="mb-0 fw-bold">
            Listado de Departamentos
        </h4>

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

<!-- FILTROS -->
<div class="p-3 border-bottom"
     style="background:#f8fafc;">

    <form id="formBusqueda"
          method="GET"
          action="{{ route('departamentos.index') }}"
          class="mb-0">

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

                    <option value="activos"
                        {{ request('estado','activos') == 'activos' ? 'selected' : '' }}>
                        Activos
                    </option>

                    <option value="inactivos"
                        {{ request('estado') == 'inactivos' ? 'selected' : '' }}>
                        Inactivos
                    </option>

                    <option value="todos"
                        {{ request('estado') == 'todos' ? 'selected' : '' }}>
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
                               href="{{ route('departamentos.imprimir',['estado'=>'activos']) }}"
                               target="_blank">
                                Departamentos activos
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('departamentos.imprimir',['estado'=>'inactivos']) }}"
                               target="_blank">
                                Departamentos inactivos
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item"
                               href="{{ route('departamentos.imprimir',['estado'=>'todos']) }}"
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

        <thead>

            <tr>

                <th width="60">#</th>

                <th width="120">Código</th>

                <th>Departamento</th>

                <th width="140" class="text-center">
                    Empleados
                </th>

                <th width="140">
                    Estado
                </th>

                <th width="260">
                    Acciones
                </th>

            </tr>

        </thead>

        <tbody id="tablaDepartamentos">

            @forelse($departamentos as $i => $dep)

            <tr>

                <td>
                    {{ $departamentos->firstItem() + $i }}
                </td>

                <td>

                    <span class="codigo-depto">
                        {{ $dep->codigo }}
                    </span>

                </td>

                <td>
                    {{ $dep->nombre }}
                </td>

                <td class="text-center">

                    <span class="cantidad-empleados">
                        {{ $dep->empleados_funcionales_count ?? 0 }}
                    </span>

                </td>

                <td>

                    @if($dep->activo)

                        <span class="badge-estado badge-activo">
                            Activo
                        </span>

                    @else

                        <span class="badge-estado badge-inactivo">
                            Inactivo
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

                            <button type="submit"
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

                <td colspan="6"
                    class="text-center text-muted py-4">

                    No hay departamentos registrados

                </td>

            </tr>

            @endforelse

        </tbody>

    </table>

</div>

<!-- PAGINACIÓN -->
<div class="mt-3 d-flex justify-content-center p-3">

    {{ $departamentos->links() }}

</div>

</div>

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

@endsection