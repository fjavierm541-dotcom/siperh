@extends('errors.layout')

@section('code', '503')

@section('title', 'Servicio temporalmente no disponible')

@section('message')

    SIPERH se encuentra realizando tareas de mantenimiento
    o actualización del sistema.

    <br><br>

    Intente nuevamente en unos momentos.

@endsection

@section('buttons')

    <a href="{{ route('paginas.inicio') }}"
       class="btn btn-siper">

        Volver al inicio

    </a>

@endsection