<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados por departamento</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }

        h2, h4 {
            text-align: center;
            margin: 0;
        }

        h4 {
            margin-top: 5px;
            margin-bottom: 20px;
            font-weight: normal;
        }

        .info {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e9ecef;
            font-weight: bold;
        }

        th, td {
            border: 1px solid #999;
            padding: 7px;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2>Listado de empleados por departamento</h2>
    <h4>{{ $departamento->codigo }} - {{ $departamento->nombre }}</h4>

    <div class="info">
        <strong>Jefe de departamento:</strong>
        @if($departamento->jefe)
            {{ $departamento->jefe->primer_nombre }}
            {{ $departamento->jefe->primer_apellido }}
        @else
            No asignado
        @endif
        <br>

        <strong>Total empleados:</strong>
        {{ $departamento->empleadosFuncionales->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th width="50">#</th>
                <th width="150">DNI</th>
                <th>Nombre completo</th>
                <th>Puesto</th>
            </tr>
        </thead>

        <tbody>
            @forelse($departamento->empleadosFuncionales as $i => $emp)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $emp->DNI }}</td>
                    <td>
                        {{ $emp->primer_nombre }}
                        {{ $emp->segundo_nombre }}
                        {{ $emp->primer_apellido }}
                        {{ $emp->segundo_apellido }}
                    </td>
                    <td>{{ $emp->puesto }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        Este departamento no tiene empleados asignados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>

</body>
</html>