@extends('layouts.master')

@section('title','Corrección de saldos')

@section('content')

<div class="glass-card">

    <!-- HEADER -->
    <div class="p-3 text-white"
        style="background:#2f4f6f;border-top-left-radius:18px;border-top-right-radius:18px;">

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <h5 class="mb-0">Corrección de saldos</h5>

                <small class="text-white-50">
                    Ajustes administrativos de vacaciones, compensatorios y horas acumuladas.
                </small>
            </div>

            <a href="{{ route('paginas.inicio') }}" class="btn btn-secondary btn-sm">
                Volver
            </a>

        </div>

    </div>

    <div class="p-4">

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

        <form id="formCorreccionSaldo"
              method="POST"
              action="{{ route('correcciones-saldos.store') }}">

            @csrf

            {{-- EMPLEADO --}}
            <div class="mb-3">
                <label class="form-label">Empleado</label>

                <select name="dni_empleado"
                        id="dniEmpleado"
                        class="form-select"
                        required>

                    <option value="">Seleccione un empleado</option>

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

            {{-- TIPO SALDO --}}
            <div class="mb-3">
                <label class="form-label">Tipo de saldo</label>

                <select name="tipo_saldo"
                        id="tipoSaldo"
                        class="form-select"
                        required>

                    <option value="">Seleccione un tipo</option>

                    <option value="vacaciones"
                        {{ old('tipo_saldo') == 'vacaciones' ? 'selected' : '' }}>
                        Vacaciones
                    </option>

                    <option value="compensatorios"
                        {{ old('tipo_saldo') == 'compensatorios' ? 'selected' : '' }}>
                        Compensatorios
                    </option>

                    <option value="horas"
                        {{ old('tipo_saldo') == 'horas' ? 'selected' : '' }}>
                        Horas acumuladas
                    </option>

                </select>
            </div>

            {{-- PERIODO VACACIONES --}}
            <div class="mb-3" id="bloquePeriodoVacaciones">
                <label class="form-label">Período laboral</label>

                <select name="periodo_id"
                        id="periodoSelect"
                        class="form-select">

                    <option value="">Seleccione un período</option>

                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}"
                            data-empleado="{{ $periodo->dni_empleado }}"
                            {{ old('periodo_id') == $periodo->id ? 'selected' : '' }}>

                            {{ $periodo->anio_laboral }}
                            | Disponibles:
                            {{ $periodo->dias_otorgados - $periodo->dias_usados }}
                            día(s)

                        </option>
                    @endforeach

                </select>
            </div>

            <div class="row">

                {{-- OPERACION --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label">Operación</label>

                    <select name="operacion"
                            class="form-select"
                            required>

                        <option value="">Seleccione</option>

                        <option value="sumar"
                            {{ old('operacion') == 'sumar' ? 'selected' : '' }}>
                            Sumar
                        </option>

                        <option value="restar"
                            {{ old('operacion') == 'restar' ? 'selected' : '' }}>
                            Restar
                        </option>

                    </select>
                </div>

                {{-- CANTIDAD --}}
                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        <span id="textoCantidad">Cantidad</span>
                    </label>

                    <input type="number"
                           name="cantidad"
                           min="1"
                           step="0.01"
                           class="form-control"
                           value="{{ old('cantidad') }}"
                           required>
                </div>

            </div>

            {{-- MOTIVO --}}
            <div class="mb-3">
                <label class="form-label">Motivo de la corrección</label>

                <textarea name="motivo"
                          rows="4"
                          maxlength="500"
                          class="form-control"
                          required>{{ old('motivo') }}</textarea>
            </div>

            {{-- ALERTA --}}
            <div class="alert alert-warning">

                <strong>Advertencia:</strong>

                <span id="textoAlerta">
                    Esta acción modificará el saldo seleccionado y quedará registrada permanentemente en el historial de movimientos.
                </span>

            </div>

            {{-- BOTONES --}}
            <div class="d-flex justify-content-between">

                <a href="{{ route('paginas.inicio') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="button"
                        class="btn btn-warning"
                        onclick="abrirModalConfirmacion()">

                    Registrar corrección

                </button>

            </div>

        </form>

    </div>

</div>

{{-- MODAL --}}
<div class="modal fade"
     id="modalConfirmacion"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header bg-warning">

                <h5 class="modal-title">Confirmar corrección</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                ¿Está seguro de registrar esta corrección de saldo?

                <br><br>

                Esta acción quedará almacenada permanentemente en el historial de movimientos del empleado.

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-warning"
                        id="btnConfirmarCorreccion">
                    Sí, registrar
                </button>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function(){

    const empleadoSelect = document.getElementById('dniEmpleado');
    const periodoSelect = document.getElementById('periodoSelect');
    const tipoSaldo = document.getElementById('tipoSaldo');
    const bloquePeriodo = document.getElementById('bloquePeriodoVacaciones');
    const textoCantidad = document.getElementById('textoCantidad');

    function filtrarPeriodos() {

        const dni = empleadoSelect.value;

        Array.from(periodoSelect.options).forEach(option => {

            if(option.value === ''){
                option.hidden = false;
                return;
            }

            option.hidden = option.dataset.empleado !== dni;

        });

        periodoSelect.value = '';
    }

    function actualizarVistaTipoSaldo() {

        const tipo = tipoSaldo.value;

        if(tipo === 'vacaciones') {

            bloquePeriodo.style.display = '';
            periodoSelect.required = true;
            textoCantidad.innerText = 'Cantidad de días';

        } else if(tipo === 'compensatorios') {

            bloquePeriodo.style.display = 'none';
            periodoSelect.required = false;
            periodoSelect.value = '';
            textoCantidad.innerText = 'Cantidad de días';

        } else if(tipo === 'horas') {

            bloquePeriodo.style.display = 'none';
            periodoSelect.required = false;
            periodoSelect.value = '';
            textoCantidad.innerText = 'Cantidad de horas';

        } else {

            bloquePeriodo.style.display = '';
            periodoSelect.required = false;
            textoCantidad.innerText = 'Cantidad';

        }
    }

    empleadoSelect.addEventListener('change', filtrarPeriodos);
    tipoSaldo.addEventListener('change', actualizarVistaTipoSaldo);

    filtrarPeriodos();
    actualizarVistaTipoSaldo();

    document.getElementById('btnConfirmarCorreccion')
        .addEventListener('click', function(){

            document.getElementById('formCorreccionSaldo').submit();

        });

});

function abrirModalConfirmacion()
{
    const modal = new bootstrap.Modal(
        document.getElementById('modalConfirmacion')
    );

    modal.show();
}

</script>

@endsection