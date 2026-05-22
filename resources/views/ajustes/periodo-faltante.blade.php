@extends('layouts.configuracion')

@section('title', 'Agregar período faltante')

@section('config-content')

<div class="glass-card">

    {{-- HEADER --}}
    <div class="p-3 text-white"
         style="background:#2f4f6f;border-top-left-radius:18px;border-top-right-radius:18px;">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="mb-0">
                    Agregar período faltante
                </h5>

                <small class="text-white-50">
                    Registra manualmente períodos omitidos en el historial de vacaciones.
                </small>

            </div>

        </div>

    </div>

    <div class="p-4">

        {{-- ALERTAS --}}
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

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORMULARIO --}}
        <form method="POST"
              action="{{ route('periodos.faltante.store') }}">

            @csrf

            {{-- EMPLEADO --}}
            <div class="mb-3">

                <label class="form-label">
                    Empleado
                </label>

                <select name="dni_empleado"
                        class="form-select"
                        required>

                    <option value="">
                        Seleccione un empleado
                    </option>

                    @foreach($empleados as $emp)

                        <option value="{{ $emp->DNI }}"
                            {{ old('dni_empleado') == $emp->DNI ? 'selected' : '' }}>

                            {{ $emp->primer_nombre }}
                            {{ $emp->segundo_nombre }}
                            {{ $emp->primer_apellido }}
                            {{ $emp->segundo_apellido }}
                            - {{ $emp->DNI }}

                        </option>

                    @endforeach

                </select>

            </div>

            <div class="row">

                {{-- AÑO --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Año laboral
                    </label>

                    <input type="number"
                           name="anio_laboral"
                           class="form-control"
                           min="1900"
                           value="{{ old('anio_laboral') }}"
                           required>

                </div>

                {{-- DÍAS --}}
                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Días otorgados
                    </label>

                    <input type="number"
                           name="dias_otorgados"
                           class="form-control"
                           min="1"
                           value="{{ old('dias_otorgados') }}"
                           required>

                </div>

            </div>

            {{-- MOTIVO --}}
            <div class="mb-3">

                <label class="form-label">
                    Motivo
                </label>

                <textarea name="motivo"
                          rows="4"
                          maxlength="500"
                          class="form-control"
                          required>{{ old('motivo') }}</textarea>

            </div>

            {{-- ALERTA --}}
            <div class="alert alert-warning">

                <strong>Advertencia:</strong>

                Esta acción creará un nuevo período real dentro del historial del empleado y quedará registrada permanentemente en bitácora y movimientos.

            </div>

            {{-- BOTONES --}}
            <div class="d-flex justify-content-between">

                <a href="{{ route('correcciones-saldos.create') }}"
                   class="btn btn-secondary">

                    Cancelar

                </a>

                <button type="submit"
                        class="btn btn-warning">

                    Registrar período faltante

                </button>

            </div>

        </form>

    </div>

</div>

@endsection