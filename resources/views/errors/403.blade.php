@extends('errors.layout')

@section('code', '403')

@section('title', 'Acceso denegado')

@section('message')

    No tiene permisos para acceder a esta sección del sistema.

    <br><br>

    Si considera que debería tener acceso,
    consulte con el administrador o con el personal de Recursos Humanos.

@endsection

@section('buttons')

    <a href="{{ route('paginas.inicio') }}"
       class="btn btn-siper">

        Volver al inicio

    </a>

@endsection