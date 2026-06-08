@extends('layouts.master')

@section('title', 'Permisos de Practicantes')

@section('content')

<style>
    body {
        background: linear-gradient(135deg, #1f3a56, #2d4f73);
        min-height: 100vh;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(8px);
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    }

    .card-header-custom {
        background-color: #274769;
        color: white;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
    }

    .btn-gold {
        background-color: #d4b06a;
        border: none;
        color: #1f3a56;
        font-weight: 600;
    }

    .btn-gold:hover {
        background-color: #c39a4f;
    }

    table th {
        background-color: #2d4f73 !important;
        color: white;
    }
</style>

<div class="container py-5">

```
<div class="glass-card">

    <div class="card-header-custom p-4 d-flex justify-content-between align-items-center">

        <h4 class="mb-0">
            Permisos de Practicantes
        </h4>

        <div class="d-flex gap-2">

            <a href="{{ route('permisos-practicantes.create') }}"
               class="btn btn-gold">

                + Nuevo Permiso

            </a>

            <a href="{{ route('permisos.menu') }}"
               class="btn btn-outline-light">

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

        <div class="table-responsive">

            <table class="table table-hover align-middle text-center">

                <thead>

                    <tr>

                        <th>Practicante</th>
                        <th>Modalidad</th>
                        <th>Tipo</th>
                        <th>Fecha inicial</th>
                        <th>Fecha final</th>
                        <th>Horas</th>
                        <th>Doc.</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                        <th></th>

                    </tr>

                </thead>

                <tbody>

                @forelse($permisos as $permiso)

                    <tr>

                        <td class="text-start">
                            {{ $permiso->practicante->nombre_completo ?? '-' }}
                        </td>

                        <td>
                            <span class="badge bg-info text-dark">
                                {{ ucfirst(str_replace('_',' ', $permiso->modalidad)) }}
                            </span>
                        </td>

                        <td>
                            {{ $permiso->tipo->nombre ?? '-' }}
                        </td>

                        <td>
                            {{ $permiso->fecha_inicio ? $permiso->fecha_inicio->format('d-m-Y') : '-' }}
                        </td>

                        <td>
                            {{ $permiso->fecha_fin ? $permiso->fecha_fin->format('d-m-Y') : '-' }}
                        </td>

                        <td>

                            @if($permiso->modalidad == 'horas')

                                {{ $permiso->horas }}

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            @if($permiso->documento)

                                <a href="{{ asset('storage/'.$permiso->documento) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">

                                    👁

                                </a>

                            @else

                                —

                            @endif

                        </td>

                        <td>

                            @if($permiso->estado->nombre == 'Pendiente')

                                <span class="badge bg-warning text-dark">
                                    Pendiente
                                </span>

                            @elseif($permiso->estado->nombre == 'Aprobado')

                                <span class="badge bg-success">
                                    Aprobado
                                </span>

                            @elseif($permiso->estado->nombre == 'Rechazado')

                                <span class="badge bg-danger">
                                    Rechazado
                                </span>

                            @elseif($permiso->estado->nombre == 'Cancelado')

                                <span class="badge bg-secondary">
                                    Cancelado
                                </span>

                            @endif

                        </td>

                        <td>

                            @if($permiso->estado->nombre == 'Pendiente')
                            

                                <form method="POST"
                                      action="{{ route('permisos-practicantes.cancelar', $permiso->id) }}">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger">

                                        Cancelar

                                    </button>

                                </form>

                            @endif

                            

                        </td>

                        <td>

                         <a href="{{ route('permisos-practicantes.imprimir', $permiso->id) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-primary">

                                    🖨️

                                </a>
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9">

                            No hay permisos registrados.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-3">
            {{ $permisos->links() }}
        </div>

    </div>

</div>
```

</div>

@endsection
