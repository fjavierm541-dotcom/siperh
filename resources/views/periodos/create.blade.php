@extends('layouts.master')

@section('title', 'Registro historico de Vacaciones')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #1f3a56, #2d4f73);
        min-height: 100vh;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
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
</style>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border-radius: 10px;
        border: 1px solid #ced4da;
        padding: 6px 10px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529;
        line-height: 28px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .select2-results__options {
    max-height: 350px !important;
}
</style>

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="container py-5">

    <div class="glass-card">

        <div class="card-header-custom p-4">
            <h4 class="mb-0">Registro inicial de vacaciones</h4>
        </div>

        <div class="p-4">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('periodos.store') }}">
                @csrf

                <!-- Empleado -->
                <select name="dni_empleado" id="dni_empleado" class="form-select select-empleado" required>
                    <option value="">Seleccione o busque un empleado</option>

                    @foreach($empleados as $empleado)
                        <option value="{{ $empleado->DNI }}">
                            {{ $empleado->primer_nombre }}
                            {{ $empleado->segundo_nombre }}
                            {{ $empleado->primer_apellido }}
                            {{ $empleado->segundo_apellido }}
                            - {{ $empleado->DNI }}
                        </option>
                    @endforeach
                </select>

                <div id="contenedor-periodos">

                    <div class="periodo-item border rounded p-3 mb-3">

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label>Año laboral</label>
                                <input type="number" name="anio_laboral[]" class="form-control" 
                                min="2000" max="{{ date('Y') }}" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Días otorgados</label>
                                <input type="number" name="dias_otorgados[]" class="form-control" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Días usados</label>
                                <input type="number" name="dias_usados[]" class="form-control" value="0">
                            </div>

                        </div>

                    </div>

                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">

    <div class="d-flex gap-2">

        <a href="{{ route('paginas.inicio') }}"
           class="btn btn-outline-secondary">

            Cancelar y volver

        </a>

        <button type="button"
                class="btn btn-secondary"
                onclick="agregarPeriodo()">

            + Agregar otro año

        </button>

    </div>

    <button type="submit" class="btn btn-gold px-4">

        Guardar registro

    </button>

</div>

            </form>

        </div>
    </div>

</div>

<script>

    
function agregarPeriodo() {

    let contenedor = document.getElementById('contenedor-periodos');

    let nuevo = `
        <div class="periodo-item border rounded p-3 mb-3">
            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Año laboral</label>
                    <input type="number" name="anio_laboral[]" class="form-control" min="2000" max="{{ date('Y') }}" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Días otorgados</label>
                    <input type="number" name="dias_otorgados[]" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Días usados</label>
                    <input type="number" name="dias_usados[]" class="form-control" value="0">
                </div>

            </div>
        </div>
    `;

    contenedor.insertAdjacentHTML('beforeend', nuevo);
}
</script>

<script>
document.querySelector('form').addEventListener('submit', function(e) {

    let otorgados = document.querySelectorAll('[name="dias_otorgados[]"]');
    let usados = document.querySelectorAll('[name="dias_usados[]"]');
    let anios = document.querySelectorAll('[name="anio_laboral[]"]');

    let valores = [];

    for (let i = 0; i < otorgados.length; i++) {

        let o = parseInt(otorgados[i].value) || 0;
        let u = parseInt(usados[i].value) || 0;
        let anio = anios[i].value;

        // 🔥 días usados > otorgados
        if (u > o) {
            alert('Los días usados no pueden ser mayores que los otorgados.');
            e.preventDefault();
            return;
        }

        // 🔥 duplicados
        if (valores.includes(anio)) {
            alert('No puedes repetir años laborales.');
            e.preventDefault();
            return;
        }

        valores.push(anio);
    }

    // 🔥 confirmación final
    if (!confirm('¿Estás seguro de registrar este historial? Esta acción no se puede deshacer.')) {
        e.preventDefault();
    }

});
</script>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.select-empleado').select2({
            placeholder: 'Seleccione o busque un empleado',
            width: '100%',
            language: {
                noResults: function() {
                    return 'No se encontraron empleados';
                },
                searching: function() {
                    return 'Buscando...';
                }
            }
        });
    });
</script>

@endsection