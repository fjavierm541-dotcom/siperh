@extends('layouts.master')

@section('content')

<div class="container">

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-warning">
        {{ session('error') }}
    </div>
@endif

    <div class="glass-card p-0 overflow-hidden">

        <div class="row g-0">

            <!-- PANEL IZQUIERDO (FECHA ACTUAL FIJA) -->

            <div class="col-md-3 calendario-lateral d-flex flex-column justify-content-center align-items-center">

                <div class="text-center">

                    <div id="numeroDia" class="dia-grande"></div>

                    <div id="mesActual" class="mes-texto"></div>

                </div>

            </div>


            <!-- CALENDARIO -->

            <div class="col-md-9 p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h4 class="mb-0">
                        Calendario Institucional
                    </h4>

                   <div class="d-flex gap-2">

                    @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                        @php
                            $yearActual = date('Y');
                        @endphp

                        <a href="{{ route('calendario.importar',$yearActual) }}" 
                        class="btn btn-outline-secondary">

                            + Feriados {{ $yearActual }}

                        </a>

                        <a href="{{ route('calendario.importar',$yearActual + 1) }}" 
                        class="btn btn-outline-secondary">

                            + Feriados {{ $yearActual + 1 }}

                        </a>

                        <a href="{{ route('calendario.create') }}" class="btn btn-dorado">
                            + Agregar feriado
                        </a>

                    @endif

                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        Volver
                    </a>

                </div>

                </div>

                <div id="calendar"></div>

            </div>

        </div>

    </div>

</div>



<!-- MODAL DETALLE DÍA -->

<!-- MODAL DETALLE DÍA -->
<div class="modal fade" id="modalDetalle">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Eventos del día
                </h5>

                <button class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body"
                 id="contenidoDetalle">

                Cargando...

            </div>

            <div class="modal-footer">

                

                <button type="button"
        class="btn btn-outline-danger d-none"
        id="btnEliminarFeriado">

    Eliminar feriado

</button>

            </div>

        </div>

    </div>

</div>


<!-- MODAL ELIMINAR -->
<div class="modal fade"
     id="modalEliminarFeriado"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    Eliminar feriado

                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p>

                    ¿Está seguro de eliminar este feriado?

                </p>

                <p>

                    Esta acción no podrá deshacerse.

                </p>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn-modal"
                        data-bs-dismiss="modal">

                    Cancelar

                </button>

                <form method="POST"
      id="formEliminarFeriado">

                    @csrf
                    @method('DELETE')

                    <button class="btn btn-outline-danger">

                        Eliminar

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<!-- MODAL MENSAJE -->

<div class="modal fade" id="modalMensaje">

    <div class="modal-dialog modal-sm">

        <div class="modal-content text-center p-3">

            <div id="mensajeTexto"></div>

        </div>

    </div>

</div>





@endsection



<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>



<style>

.calendario-lateral {
    background: #1f3a5f;
    color: white;
    min-height: 420px;
}

.dia-grande {
    font-size: 90px;
    font-weight: 700;
}

.mes-texto {
    font-size: 16px;
    letter-spacing: 2px;
}

.btn-dorado {
    background: #c9a227;
    color: white;
}

.fc-day-sat,
.fc-day-sun {
    background: #f1f1f1;
}

.fc .fc-day-today {
    background: rgba(201,162,39,0.15);
}

.fc-event {
    border-radius: 6px;
    border: none;
}
#modalYear .modal-content {
    border-radius: 12px;
}
</style>



<script>

let calendar;
let feriadoSeleccionado = null;

// 🔹 FECHA ACTUAL FIJA (NO CAMBIA)
document.addEventListener('DOMContentLoaded', function () {

    const hoy = new Date();

    document.getElementById('numeroDia').innerText = hoy.getDate();

    document.getElementById('mesActual').innerText =
        hoy.toLocaleString('es-ES', { month: 'long', year: 'numeric' }).toUpperCase();



    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {

        initialView: 'dayGridMonth',

        locale: 'es',

        height: 'auto',

        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },

        events: '/calendario/eventos',


        // CLICK EN DÍA → VER DETALLE
        dateClick: function(info){

            fetch('/calendario/dia?fecha=' + info.dateStr)

            .then(res => res.json())

            .then(data => {

                let html = '';
                const btnEliminar = document.getElementById('btnEliminarFeriado');

                if(data.length === 0){

    html = "<p class='text-center text-muted'>No hay feriados</p>";

    feriadoSeleccionado = null;

    btnEliminar.classList.add('d-none');

}else{

                    data.forEach(d => {

                        html += `
                            <div class="mb-2">
                                <strong>${d.titulo}</strong><br>
                                <small>${d.descripcion ?? ''}</small>
                            </div>
                        `;
                    });

                }

                document.getElementById('contenidoDetalle').innerHTML = html;

if (data.length > 0) {
    feriadoSeleccionado = data[0].id;
    btnEliminar.classList.remove('d-none');
}


new bootstrap.Modal(document.getElementById('modalDetalle')).show();

            });

        },

        // CLICK EVENTO → EDITAR
        eventClick: function(info){

            @if(in_array(auth()->user()->rol, ['superadmin', 'rrhh']))

                window.location = '/calendario/' + info.event.id + '/edit';

            @endif

        }

    });

    calendar.render();

   document.getElementById('btnEliminarFeriado').addEventListener('click', function () {

   if (!feriadoSeleccionado) {

    alert('En este día no hay un feriado registrado.');

    return;

}

    document.getElementById('formEliminarFeriado').action =
        '/calendario/' + feriadoSeleccionado;

    // Cerrar modal de detalle
    bootstrap.Modal.getInstance(
        document.getElementById('modalDetalle')
    ).hide();

    // Abrir modal de confirmación
    new bootstrap.Modal(
        document.getElementById('modalEliminarFeriado')
    ).show();

});

});



</script>