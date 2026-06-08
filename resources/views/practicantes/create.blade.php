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

<style>

.select2-container {
    width: 100% !important;
}

.select2-container--default .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: .375rem !important;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}

.select2-results__options {
    max-height: 350px !important;
}

</style>

<div class="p-4">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



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
                       class="form-control @error('nombre_completo') is-invalid @enderror"
                       value="{{ old('nombre_completo') }}"
                       required
                       minlength="3"
                       maxlength="200">

                @error('nombre_completo')
                    <small class="text-danger d-block">
                        {{ $message }}
                    </small>
                @enderror

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    DNI *
                </label>

                <input type="text"
                       id="dni_practicante"
                       name="dni_practicante"
                       class="form-control @error('dni_practicante') is-invalid @enderror"
                       value="{{ old('dni_practicante') }}"
                       required
                       minlength="13"
                       maxlength="13"
                       inputmode="numeric"
                       placeholder="0000000000000"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57">

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
                       class="form-control @error('institucion') is-invalid @enderror"
                       value="{{ old('institucion') }}"
                       required
                       minlength="3"
                       maxlength="150">

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
                       class="form-control @error('correo') is-invalid @enderror"
                       value="{{ old('correo') }}"
                       maxlength="255"
                       placeholder="correo@ejemplo.com">

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

                <input type="text"
                       id="horas_requeridas"
                       name="horas_requeridas"
                       class="form-control @error('horas_requeridas') is-invalid @enderror"
                       value="{{ old('horas_requeridas') }}"
                       inputmode="numeric"
                       maxlength="4"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57">

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
                       class="form-control @error('fecha_inicio') is-invalid @enderror"
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
                       class="form-control @error('fecha_fin') is-invalid @enderror"
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
                    id="departamento_id"
                    class="form-select select-departamento @error('departamento_id') is-invalid @enderror"
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function() {

    $('.select-departamento').select2({

        placeholder: 'Seleccione o busque un departamento',

        width: '100%',

        language: {

            noResults: function() {

                return 'No se encontraron departamentos';

            },

            searching: function() {

                return 'Buscando...';

            }

        }
        

    });

    const dni =
document.getElementById('dni_practicante');

dni.addEventListener('input', function(){

    this.value =
        this.value.replace(/[^0-9]/g,'');

});

const horas =
document.getElementById('horas_requeridas');

horas.addEventListener('input', function(){

    this.value =
        this.value.replace(/[^0-9]/g,'');

});

});

</script>



@endsection
