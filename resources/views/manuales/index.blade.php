@extends('layouts.configuracion')

@section('title', 'Manuales')

@section('config-content')

<div class="glass-card overflow-hidden">

```
<div class="p-4 text-white"
     style="background:#27496d;">

    <h4 class="fw-bold mb-1">

        📚 Manuales del sistema

    </h4>

    <small>

        Consulta y descarga los manuales oficiales de SIPERH.

    </small>

</div>


<div class="p-4">

    <div class="row g-4">


        <div class="col-md-4">

            <div class="glass-card h-100 p-4">

                <h5 class="fw-bold">

                    📘 Manual de usuario para administradores

                </h5>

                <p class="text-muted">

                    Guía completa para superadministradores y personal de Recursos Humanos.

                </p>

                <a href="{{ asset('manuales/Manual_de_usuario_administrador.pdf') }}"
                   download
                   class="btn btn-primary-custom">

                    Descargar

                </a>

            </div>

        </div>


        <div class="col-md-4">

            <div class="glass-card h-100 p-4">

                <h5 class="fw-bold">

                    📗 Manual de usuario para jefes de departamento

                </h5>

                <p class="text-muted">

                    Procedimientos y funciones disponibles para los jefes de departamento.

                </p>

                <a href="{{ asset('manuales/Manual_de_usuario_jefes.pdf') }}"
                   download
                   class="btn btn-primary-custom">

                    Descargar

                </a>

            </div>

        </div>


        <div class="col-md-4">

            <div class="glass-card h-100 p-4">

                <h5 class="fw-bold">

                    ⚙️ Manual de instalación

                </h5>

                <p class="text-muted">

                    Requisitos, instalación y configuración del sistema SIPERH.

                </p>

                <a href="{{ asset('manuales/Manual_de_instalacion.pdf') }}"
                   download
                   class="btn btn-primary-custom">

                    Descargar

                </a>

            </div>

        </div>


    </div>

</div>
```

</div>

@endsection
