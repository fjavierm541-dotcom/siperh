<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Imprimir departamentos
    </title>

    <style>

        @page {
            size: letter;
            margin: 24px;
        }

        body {

            font-family: Arial, sans-serif;
            color: #222;
            font-size: 11px;
            margin: 0;
        }

        .header {

            text-align: center;

            margin-bottom: 18px;
        }

        .titulo {

            font-size: 26px;

            font-weight: bold;

            color: #183b63;

            margin-bottom: 4px;

            letter-spacing: .5px;
        }

        .subtitulo {

            font-size: 11px;

            color: #555;

            margin-bottom: 12px;
        }

        .info {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 12px;

            font-size: 11px;

            color: #444;
        }

        .linea {

            height: 3px;

            background: #24476b;

            margin-bottom: 18px;

            border-radius: 10px;
        }

        table {

            width: 100%;

            border-collapse: collapse;
        }

        thead tr {

            background: #24476b;

            color: white;
        }

        th {

            padding: 10px 8px;

            border: 1px solid #d4dbe3;

            text-align: center;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: .3px;
        }

        td {

            border: 1px solid #dfe5ec;

            padding: 10px 9px;

            font-size: 11px;

            vertical-align: middle;
        }

        tbody tr:nth-child(even) {

            background: #f7f9fc;
        }

        .text-center {

            text-align: center;
        }

        .codigo {

            font-weight: bold;

            color: #183b63;
        }

        .estado-activo {

            color: #198754;

            font-weight: bold;
        }

        .estado-inactivo {

            color: #dc3545;

            font-weight: bold;
        }

        .footer {

            margin-top: 14px;

            text-align: right;

            font-size: 10px;

            color: #666;
        }

    </style>

</head>

<body>

    <div class="header">

        <div class="titulo">
            LISTADO GENERAL DE DEPARTAMENTOS
        </div>

        <div class="subtitulo">
            Municipalidad de Danlí
        </div>

    </div>

    <div class="info">

        <div>

            <strong>Fecha de impresión:</strong>
            {{ now()->format('d/m/Y h:i A') }}

        </div>

        <div>

            @if($estado === 'activos')

                <strong>Departamentos activos</strong>

            @elseif($estado === 'inactivos')

                <strong>Departamentos inactivos</strong>

            @else

                <strong>Todos los departamentos</strong>

            @endif

        </div>

        <div>

            <strong>Total:</strong>
            {{ $departamentos->count() }}

        </div>

    </div>

    <div class="linea"></div>

    <table>

        <thead>

            <tr>

                <th width="50">
                    #
                </th>

                <th width="110">
                    Código
                </th>

                <th>
                    Departamento
                </th>

                <th width="120">
                    Empleados
                </th>

                <th width="120">
                    Estado
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($departamentos as $i => $dep)

                <tr>

                    <td class="text-center">

                        {{ $i + 1 }}

                    </td>

                    <td class="text-center codigo">

                        {{ $dep->codigo }}

                    </td>

                    <td>

                        {{ $dep->nombre }}

                    </td>

                    <td class="text-center">

                        {{ $dep->empleados_funcionales_count ?? 0 }}

                    </td>

                    <td class="text-center">

                        @if($dep->activo)

                            <span class="estado-activo">
                                Activo
                            </span>

                        @else

                            <span class="estado-inactivo">
                                Inactivo
                            </span>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5"
                        class="text-center">

                        No hay departamentos para mostrar.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">

        SIPERH · Sistema Integrado de Recursos Humanos

    </div>

<script>

    window.addEventListener('load', function () {

        window.print();

    });

</script>

</body>
</html>