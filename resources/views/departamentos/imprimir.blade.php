<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir departamentos</title>

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
            margin-bottom: 25px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>


<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script> 

<body>

    

    <h2>Listado de departamentos</h2>

    <h4>
        @if($estado === 'activos')
            Departamentos activos
        @elseif($estado === 'inactivos')
            Departamentos inactivos
        @else
            Todos los departamentos
        @endif
    </h4>

    <table>
        <thead>
            <tr>
                <th width="50">#</th>
                <th width="100">Código</th>
                <th>Departamento</th>
                <th width="100">Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse($departamentos as $i => $dep)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $dep->codigo }}</td>
                    <td>{{ $dep->nombre }}</td>
                    <td>
                        {{ $dep->activo ? 'Activo' : 'Inactivo' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">
                        No hay departamentos para mostrar.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>