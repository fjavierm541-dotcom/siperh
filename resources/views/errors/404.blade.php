@extends('errors.layout')

@section('code', '404')

@section('title', 'Página no encontrada')

@section('message')

    La página solicitada no existe o fue movida.
    <br>
    Verifique la dirección e intente nuevamente.

@endsection

@section('buttons')

    <a href="{{ route('paginas.inicio') }}"
       class="btn btn-siper">

        Volver al inicio

    </a>

@endsection