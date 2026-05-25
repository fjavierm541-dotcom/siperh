@extends('errors.layout')

@section('code', '419')

@section('title', 'Página expirada')

@section('message')

    Tu sesión ha expirado por seguridad o inactividad.
    <br>
    Inicia sesión nuevamente para continuar utilizando el sistema.

@endsection

@section('buttons')

    <a href="{{ route('login') }}"
       class="btn btn-siper">

        Ir al inicio de sesión

    </a>

@endsection