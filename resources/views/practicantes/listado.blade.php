<!DOCTYPE html>

<html lang="es">

<head>
<meta charset="UTF-8">

<title>{{ $titulo }}</title>

<style>

    @page {
        size: letter landscape;
        margin: 10mm;
    }

    body{
        font-family: Arial, sans-serif;
        font-size: 9px;
        color:#111;
    }

    table{
        width:100%;
        border-collapse: collapse;
    }

    th,td{
        border:1px solid #333;
        padding:5px;
        vertical-align: middle;
    }

    th{
        background:#1f3a56;
        color:white;
        text-align:center;
        font-size:9px;
    }

    .header-table td{
        border:none;
    }

    .logo{
        width:58px;
        height:58px;
        object-fit:contain;
    }

    .titulo{
        font-size:15px;
        font-weight:bold;
        text-align:center;
        color:#1f3a56;
        text-transform:uppercase;
    }

    .subtitulo{
        text-align:center;
        font-size:10px;
        margin-top:4px;
    }

    .meta{
        margin:10px 0;
        font-size:9px;
    }

    .text-center{
        text-align:center;
    }

    .badge-activo{
        font-weight:bold;
        color:#0f7a34;
    }

    .badge-inactivo{
        font-weight:bold;
        color:#b42318;
    }

    .footer{
        margin-top:12px;
        font-size:8px;
        text-align:right;
    }

</style>

</head>

<body>

<table class="header-table">

```
<tr>

    <td style="width:15%;text-align:left;">
        <img src="{{ public_path('img/logoescudo.png') }}"
             class="logo">
    </td>

    <td style="width:70%;">

        <div class="titulo">

            {{ $titulo }}

        </div>

        <div class="subtitulo">

            Municipalidad de Danlí · Departamento de Recursos Humanos

        </div>

    </td>

    <td style="width:15%;text-align:right;">

        <img src="{{ public_path('img/logomuni.png') }}"
             class="logo">

    </td>

</tr>
```

</table>

<div class="meta">

```
<strong>Fecha de impresión:</strong>

{{ now()->format('d/m/Y h:i A') }}

&nbsp; | &nbsp;

<strong>Total de practicantes:</strong>

{{ $practicantes->count() }}
```

</div>

<table>

```
<thead>

    <tr>

        <th>#</th>
        <th>Nombre completo</th>
        <th>DNI</th>
        <th>Institución</th>
        <th>Correo</th>
        <th>Horas</th>
        <th>Fecha inicio</th>
        <th>Fecha fin</th>
        <th>Departamento</th>
        <th>Estado</th>

    </tr>

</thead>

<tbody>

    @forelse($practicantes as $index => $practicante)

        <tr>

            <td class="text-center">
                {{ $index + 1 }}
            </td>

            <td>
                {{ $practicante->nombre_completo }}
            </td>

            <td class="text-center">
                {{ $practicante->dni_practicante ?: '-' }}
            </td>

            <td>
                {{ $practicante->institucion }}
            </td>

            <td>
                {{ $practicante->correo ?: '-' }}
            </td>

            <td class="text-center">
                {{ $practicante->horas_requeridas ?: '-' }}
            </td>

            <td class="text-center">
                {{ \Carbon\Carbon::parse($practicante->fecha_inicio)->format('d/m/Y') }}
            </td>

            <td class="text-center">
                {{ $practicante->fecha_fin ? \Carbon\Carbon::parse($practicante->fecha_fin)->format('d/m/Y') : '-' }}
            </td>

            <td>
                {{ $practicante->departamento->nombre ?? '-' }}
            </td>

            <td class="text-center">

                <span class="{{ $practicante->activo ? 'badge-activo' : 'badge-inactivo' }}">

                    {{ $practicante->activo ? 'Activo' : 'Inactivo' }}

                </span>

            </td>

        </tr>

    @empty

        <tr>

            <td colspan="10"
                class="text-center">

                No hay practicantes para mostrar.

            </td>

        </tr>

    @endforelse

</tbody>
```

</table>

<div class="footer">

SIPERH · Reporte generado automáticamente

</div>

</body>

</html>
