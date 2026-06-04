@extends('layouts.configuracion')

@section('title', 'Nuevo Practicante')

@section('config-content')

<div class="glass-card overflow-hidden">

    <div class="p-4 text-white"
         style="background:#27496d;">

        <h4 class="fw-bold mb-1">
            Nuevo practicante
        </h4>

        <small>
            Registro de estudiantes en práctica profesional.
        </small>

    </div>

    <div class="p-4">

        <form action="{{ route('practicantes.store') }}"
              method="POST">

            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Nombre completo *
                    </label>

                    <input type="text"
                           name="nombre_completo"
                           class="form-control"
                           value="{{ old('nombre_completo') }}"
                           required>

                    @error('nombre_completo')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        DNI
                    </label>

                    <input type="text"
                           name="dni_practicante"
                           class="form-control"
                           value="{{ old('dni_practicante') }}">

                    @error('dni_practicante')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Institución *
                    </label>

                    <input type="text"
                           name="institucion"
                           class="form-control"
                           value="{{ old('institucion') }}"
                           required>

                    @error('institucion')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Correo
                    </label>

                    <input type="email"
                           name="correo"
                           class="form-control"
                           value="{{ old('correo') }}">

                    @error('correo')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Horas requeridas
                    </label>

                    <input type="number"
                           name="horas_requeridas"
                           class="form-control"
                           value="{{ old('horas_requeridas') }}"
                           min="1">

                    @error('horas_requeridas')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Fecha inicio *
                    </label>

                    <input type="date"
                           name="fecha_inicio"
                           class="form-control"
                           value="{{ old('fecha_inicio') }}"
                           required>

                    @error('fecha_inicio')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Fecha finalización
                    </label>

                    <input type="date"
                           name="fecha_fin"
                           class="form-control"
                           value="{{ old('fecha_fin') }}">

                    @error('fecha_fin')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="col-md-12 mb-4">

                    <label class="form-label">
                        Departamento asignado *
                    </label>

                    <select name="departamento_id"
                            class="form-select"
                            required>

                        <option value="">
                            Seleccione un departamento
                        </option>

                        @foreach($departamentos as $departamento)

                            <option value="{{ $departamento->id }}"
                                {{ old('departamento_id') == $departamento->id ? 'selected' : '' }}>

                                {{ $departamento->nombre }}

                            </option>

                        @endforeach

                    </select>

                    @error('departamento_id')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

            </div>

            <div class="border-top pt-3">

                <button type="submit"
                        class="btn btn-primary-custom">
                    Guardar practicante
                </button>

                <a href="{{ route('practicantes.index') }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>

</div>

@endsection