@extends('errors.layout')

@section('code', '504')

@section('title', 'Tiempo de espera agotado')

@section('message')

    Oops, parece que ocurrió un problema de comunicación.

    <br>

    El servidor tardó demasiado en responder a la solicitud.

    <br><br>

    Intente nuevamente en unos momentos.

@endsection

@section('buttons')

    <a href="javascript:location.reload()"
       class="btn btn-siper">

        Reintentar

    </a>

    <a href="{{ route('paginas.inicio') }}"
       class="btn btn-outline-siper">

        Volver al inicio

    </a>

@endsection