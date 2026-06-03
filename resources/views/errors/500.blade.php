@extends('errors.layout')

@section('code', '500')

@section('title', 'Error interno del sistema')

@section('message')

    Oops, parece que ocurrió un error inesperado.
    <br>
    El sistema no pudo completar la operación solicitada.
    <br><br>
    Si el problema persiste, contacte al administrador del sistema.

@endsection

@section('buttons')

    <a href="{{ route('paginas.inicio') }}"
       class="btn btn-siper">

        Volver al inicio

    </a>

@endsection