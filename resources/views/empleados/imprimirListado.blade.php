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

    body {
        font-family: Arial, sans-serif;
        font-size: 9px;
        color: #111;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #333;
        padding: 5px;
        vertical-align: middle;
    }

    th {
        background: #1f3a56;
        color: white;
        text-align: center;
        font-size: 9px;
    }

    .header-table td {
        border: none;
    }

    .logo {
        width: 58px;
        height: 58px;
        object-fit: contain;
    }

    .titulo {
        font-size: 15px;
        font-weight: bold;
        text-align: center;
        color: #1f3a56;
        text-transform: uppercase;
    }

    .subtitulo {
        text-align: center;
        font-size: 10px;
        margin-top: 4px;
    }

    .meta {
        margin: 10px 0;
        font-size: 9px;
    }

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .badge-activo {
        font-weight: bold;
        color: #0f7a34;
    }

    .badge-inactivo {
        font-weight: bold;
        color: #b42318;
    }

    .footer {
        margin-top: 12px;
        font-size: 8px;
        text-align: right;
    }
</style>
</head>

<body>

<table class="header-table">
    <tr>
        <td style="width: 15%; text-align: left;">
            <img src="{{ public_path('img/logoescudo.png') }}" class="logo">
        </td>

        <td style="width: 70%;">
            <div class="titulo">{{ $titulo }}</div>
            <div class="subtitulo">
                Municipalidad de Danlí · Departamento de Recursos Humanos
            </div>
        </td>

        <td style="width: 15%; text-align: right;">
            <img src="{{ public_path('img/logomuni.png') }}" class="logo">
        </td>
    </tr>
</table>

<div class="meta">
    <strong>Fecha de impresión:</strong> {{ now()->format('d/m/Y h:i A') }}
    &nbsp; | &nbsp;
    <strong>Total de empleados:</strong> {{ $empleados->count() }}
</div>

<table>
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 8%;">Código</th>
            <th style="width: 22%;">Nombre completo</th>
            <th style="width: 12%;">DNI</th>
            <th style="width: 8%;">Sexo</th>
            <th style="width: 13%;">Tipo</th>
            <th style="width: 13%;">Depto. Administrativo</th>
            <th style="width: 13%;">Depto. Funcional</th>
            <th style="width: 12%;">Puesto</th>
            <th style="width: 8%;">Estado</th>
        </tr>
    </thead>

    <tbody>
        @forelse($empleados as $index => $empleado)
            @php
                $estadoEmpleado = strtolower(trim($empleado->estado_empleado ?? 'activo'));

                $nombreCompleto = trim(
                    ($empleado->primer_nombre ?? '') . ' ' .
                    ($empleado->segundo_nombre ?? '') . ' ' .
                    ($empleado->primer_apellido ?? '') . ' ' .
                    ($empleado->segundo_apellido ?? '')
                );
            @endphp

            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $empleado->codigo ?? '-' }}</td>
                <td class="text-left">{{ $nombreCompleto ?: '-' }}</td>
                <td class="text-center">{{ $empleado->DNI ?? '-' }}</td>
                <td class="text-center">{{ $empleado->sexo ?? '-' }}</td>
                <td class="text-center">{{ $empleado->tipo ?? '-' }}</td>
                <td>{{ $empleado->departamento->nombre ?? 'No asignado' }}</td>
                <td>{{ $empleado->departamentoFuncional->nombre ?? 'Sin asignación' }}</td>
                <td>{{ $empleado->puesto ?? '-' }}</td>
                <td class="text-center">
                    <span class="{{ $estadoEmpleado === 'inactivo' ? 'badge-inactivo' : 'badge-activo' }}">
                        {{ ucfirst($estadoEmpleado) }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">
                    No hay empleados para mostrar.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    SIPERH · Reporte generado automáticamente
</div>

</body>
</html>