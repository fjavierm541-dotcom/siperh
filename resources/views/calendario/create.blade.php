@extends('layouts.master')

@section('content')

<style>
    .form-check-input {
    cursor: pointer;
}

.form-check-label {
    cursor: pointer;
}
</style>

<div class="container">

    <div class="glass-card p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

    <h4 class="mb-0">
        Nuevo feriado
    </h4>

    <button class="btn btn-outline-primary btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalAyuda">

    <i class="fas fa-circle-question"></i>
    Ayuda

</button>

</div>

        <form method="POST" action="{{ route('calendario.store') }}">
        @csrf

        {{-- ERRORES --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- 🔹 DATOS GENERALES --}}
        <div class="mb-4">

            <h6 class="text-muted mb-3">Información del día</h6>

            <div class="row">

                <div class="col-md-6 mb-2">
                    <label>Título</label>
                    <input type="text"
                           name="titulo"
                           value="{{ old('titulo') }}"
                           class="form-control"
                           placeholder="Ingrese el nombre del feriado"
                           required
                           maxlength="150"
                           pattern="[A-Za-z0-9ÁÉÍÓÚáéíóúñÑ\s\-]+">
                </div>

                <div class="col-md-3 mb-2">
                    <label>Fecha inicio</label>
                    <input type="date"
                           name="fecha_inicio"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-3 mb-2">
                    <label>Fecha fin</label>
                    <input type="date"
                           name="fecha_fin"
                           class="form-control">
                </div>

            </div>

            <div class="row mt-2">

                <div class="col-md-6">
                    <label>Tipo de feriado</label>
                    <select name="origen" class="form-control" required>
                        <option value="nacional">Nacional</option>
                        <option value="local">Local</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Tipo de afectación</label>

                    <select name="tipo_afectacion" class="form-control" required>

                        <option value="no_laborable">
                            Informativo (no afecta días)
                        </option>

                        <option value="descuento">
                            Laborable con excepciones
                        </option>

                    </select>
                </div>

            </div>

        </div>

        <div class="mb-3">
    <label class="form-label">
        <strong>Departamentos que sí laborarán</strong>
    </label>

    <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto; background: #f9f9f9;">

        @foreach($departamentos as $dep)
            <div class="form-check mb-1">
                <input 
                    class="form-check-input" 
                    type="checkbox" 
                    name="departamentos[]" 
                    value="{{ $dep->id }}" 
                    id="dep{{ $dep->id }}"
                >

                <label class="form-check-label" for="dep{{ $dep->id }}">
                    {{ $dep->nombre }}
                </label>
            </div>
        @endforeach

    </div>


</div>


        {{-- 🔹 DESCRIPCIÓN --}}
        <div class="mb-4">

            <label>Descripción</label>

            <textarea name="descripcion"
                      class="form-control"
                      rows="3"
                      placeholder="Descripción o motivo del feriado..."
                      required></textarea>

        </div>


        {{-- BOTÓN --}}
        
        <div class="d-flex justify-content-end">
            
        <a href="{{ route('calendario.index') }}" class="btn btn-outline-secondary mb-3">
    ← Volver al calendario
</a>

            <button class="btn btn-outline-secondary mb-3">
                Guardar
            </button>

        </div>

        </form>

    </div>

</div>



<script>
document.addEventListener('DOMContentLoaded', function(){

    const inicio = document.querySelector('input[name="fecha_inicio"]');
    const fin = document.querySelector('input[name="fecha_fin"]');

    inicio.addEventListener('change', function(){

        if(inicio.value){
            fin.min = inicio.value; // 🔥 clave
        }

        // si fecha fin es menor, la limpia
        if(fin.value && fin.value < inicio.value){
            fin.value = '';
        }

    });

});
</script>

<!-- Modal Ayuda -->
<!-- MODAL AYUDA -->
<div class="modal fade" id="modalAyuda" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Ayuda del calendario institucional
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <h6><strong>Tipos de afectación</strong></h6>

                <p>
                    <strong>Informativo (no afecta días)</strong>
                    <br>
                    Solo muestra información en el calendario institucional.
                    No descuenta días y no genera días compensatorios.
                </p>

                <p>
                    <strong>Laborable con excepciones</strong>
                    <br>
                    Los departamentos seleccionados trabajarán y recibirán un día compensatorio.
                    Los departamentos no seleccionados descansarán y se les descontará un día.
                </p>

                <hr>

                <h6><strong>Casos más comunes</strong></h6>

                <br>

                <p>
                    <strong>1. Informativo</strong>
                    <br>

                    <strong>Tipo de afectación requerido:</strong>
                    Informativo (no afecta días).

                    <br>

                    <strong>Resultado:</strong>
                    El feriado únicamente aparecerá en el calendario institucional.
                    No se descontarán días ni se generarán compensatorios.
                </p>

                <hr>

                <p>
                    <strong>2. Descuento total</strong>
                    <br>

                    <strong>Tipo de afectación requerido:</strong>
                    Laborable con excepciones.

                    <br>

                    <strong>Configuración:</strong>
                    No seleccionar ningún departamento.

                    <br>

                    <strong>Resultado:</strong>
                    Todos los empleados descansarán y se les descontará un día.
                </p>

                <hr>

                <p>
                    <strong>3. Descuento parcial y compensatorio parcial</strong>
                    <br>

                    <strong>Tipo de afectación requerido:</strong>
                    Laborable con excepciones.

                    <br>

                    <strong>Configuración:</strong>
                    Seleccionar únicamente los departamentos que trabajarán.

                    <br>

                    <strong>Resultado:</strong>

                    <br>

                    • Los departamentos seleccionados trabajarán y recibirán un día compensatorio.

                    <br>

                    • Los departamentos no seleccionados descansarán y se les descontará un día.
                </p>

                <hr>

                <p>
                    <strong>4. Compensatorio total</strong>
                    <br>

                    <strong>Tipo de afectación requerido:</strong>
                    Laborable con excepciones.

                    <br>

                    <strong>Configuración:</strong>
                    Seleccionar todos los departamentos.

                    <br>

                    <strong>Resultado:</strong>
                    Todos los empleados trabajarán y recibirán un día compensatorio.
                </p>

                <hr>

                <h6><strong>Información adicional</strong></h6>

                <p>
                    Para una explicación más detallada del módulo de calendario,
                    consulte los documentos disponibles en:
                </p>

                <p>
                    <strong>
                        Panel de Ajustes → Manuales de uso
                    </strong>
                </p>

                <p>
                    Documento de apoyo:
                </p>

                <ul>

                    <li>
                        Manual de Usuario para Administradores.
                    </li>

                </ul>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn-modal"
                        data-bs-dismiss="modal">

                    Entendido

                </button>

            </div>

        </div>

    </div>

</div>

@endsection