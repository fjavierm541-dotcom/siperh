@extends('layouts.master')

@section('title','Mi Departamento')

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
                    Mi departamento
                </small>
            </div>

            <div class="d-flex flex-wrap gap-2">

                

                <a href="{{ route('paginas.inicio') }}"
                   class="btn btn-secondary btn-sm">
                    Volver
                </a>

            </div>

        </div>

    </div>

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
                        <th>Puesto</th>
                        <th>Días disponibles</th>
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

                            <td>{{ $emp->puesto }}</td>
                            <td>
                                {{ $emp->dias_disponibles }} días
                                y
                                {{ $emp->horas_disponibles }} horas
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

@endsection