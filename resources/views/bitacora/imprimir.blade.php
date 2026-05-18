<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Bitácora del Sistema</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header{
            width: 100%;
            margin-bottom: 18px;
        }

        .titulo{
            font-size: 20px;
            font-weight: bold;
            color: #274769;
            margin-bottom: 4px;
        }

        .subtitulo{
            font-size: 11px;
            color: #666;
        }

        .fecha{
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th{
            background-color: #274769;
            color: white;
            padding: 8px;
            font-size: 10px;
            text-align: center;
        }

        table td{
            border: 1px solid #d6d6d6;
            padding: 7px;
            vertical-align: top;
            font-size: 10px;
        }

        .text-center{
            text-align: center;
        }

        .small{
            font-size: 9px;
            color: #666;
        }

        .modulo{
            background-color: #e9eef4;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: bold;
            color: #274769;
        }

        .footer{
            position: fixed;
            bottom: -5px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

    </style>

</head>

<body>

    {{-- ENCABEZADO --}}
    <table class="header">
        <tr>
            <td>
                <div class="titulo">
                    Bitácora del Sistema
                </div>

                <div class="subtitulo">
                    Registro general de acciones importantes realizadas en SIPERH
                </div>
            </td>

            <td class="fecha">
                Generado:
                <br>
                {{ now()->format('d/m/Y h:i A') }}
            </td>
        </tr>
    </table>

    {{-- TABLA --}}
    <table>

        <thead>

            <tr>
                <th width="11%">Fecha</th>
                <th width="14%">Usuario</th>
                <th width="10%">Rol</th>
                <th width="13%">Acción</th>
                <th width="11%">Módulo</th>
                <th width="25%">Descripción</th>
                <th width="8%">IP</th>
                <th width="8%">Referencia</th>
            </tr>

        </thead>

        <tbody>

            @forelse($bitacoras as $item)

                <tr>

                    {{-- FECHA --}}
                    <td class="text-center">

                        {{ $item->created_at?->format('d/m/Y') }}

                        <div class="small">
                            {{ $item->created_at?->format('h:i A') }}
                        </div>

                    </td>

                    {{-- USUARIO --}}
                    <td>

                        <strong>
                            {{ $item->usuario_nombre ?? 'Sistema' }}
                        </strong>

                        <div class="small">
                            {{ $item->empleado_dni ?? 'Sin DNI' }}
                        </div>

                    </td>

                    {{-- ROL --}}
                    <td class="text-center">

                        {{ ucfirst($item->rol_usuario ?? 'sistema') }}

                    </td>

                    {{-- ACCION --}}
                    <td>

                        {{ str_replace('_', ' ', ucfirst($item->accion)) }}

                        <div class="small">
                            {{ $item->metodo }}
                        </div>

                    </td>

                    {{-- MODULO --}}
                    <td class="text-center">

                        <span class="modulo">
                            {{ ucfirst($item->modulo) }}
                        </span>

                    </td>

                    {{-- DESCRIPCION --}}
                    <td>

                        {{ $item->descripcion }}

                    </td>

                    {{-- IP --}}
                    <td class="text-center">

                        {{ $item->ip_equipo ?? '-' }}

                    </td>

                    {{-- REFERENCIA --}}
                    <td class="text-center">

                        @if($item->referencia_tipo || $item->referencia_id)

                            {{ $item->referencia_tipo ?? '-' }}
                            <br>
                            #{{ $item->referencia_id ?? '-' }}

                        @else

                            —

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="8" class="text-center">

                        No se encontraron registros.

                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    {{-- FOOTER --}}
    <div class="footer">

        SIPERH Danlí — Sistema de Personal y Recursos Humanos

    </div>

</body>
</html>