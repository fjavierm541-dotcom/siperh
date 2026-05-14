@extends('layouts.master')

@section('title','Editar departamento')

@section('content')

<div class="glass-card">

    <div class="p-3 text-white"
        style="background:#2f4f6f;border-top-left-radius:18px;border-top-right-radius:18px;">

        <div class="d-flex justify-content-between">

            <h5 class="mb-0">
                Editar departamento
            </h5>

            <a href="{{ route('departamentos.index') }}"
                class="btn btn-secondary btn-sm">
                Volver
            </a>

        </div>

    </div>

    <div class="p-4">

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('departamentos.update', $departamento->id) }}">
            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-4">

                    <label class="form-label">
                        Código
                    </label>

                    <input type="text"
                        name="codigo"
                        id="codigoDepto"
                        maxlength="3"
                        value="{{ old('codigo', $departamento->codigo) }}"
                        class="form-control @error('codigo') is-invalid @enderror"
                        required>

                    @error('codigo')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

                <div class="col-md-8">

                    <label class="form-label">
                        Nombre del departamento
                    </label>

                    <input type="text"
                        name="nombre"
                        id="nombreDepto"
                        maxlength="150"
                        value="{{ old('nombre', $departamento->nombre) }}"
                        class="form-control @error('nombre') is-invalid @enderror"
                        required>

                    @error('nombre')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror

                </div>

            </div>

            <div class="mt-3">

                <label class="form-label">
                    Descripción
                </label>

                <textarea
                    name="descripcion"
                    id="descripcionDepto"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    maxlength="255"
                    rows="3">{{ old('descripcion', $departamento->descripcion) }}</textarea>

                @error('descripcion')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

            </div>

            <div class="mt-3">

                <label class="form-label">
                    Depende de
                </label>

                <select name="departamento_padre_id"
                    class="form-control @error('departamento_padre_id') is-invalid @enderror">

                    <option value="">
                        Ninguno
                    </option>

                    @foreach($padres as $dep)

                        <option value="{{ $dep->id }}"
                            {{ old('departamento_padre_id', $departamento->departamento_padre_id) == $dep->id ? 'selected' : '' }}>

                            {{ $dep->codigo }} - {{ $dep->nombre }}

                        </option>

                    @endforeach

                </select>

                @error('departamento_padre_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

            </div>

            <div class="mt-4 d-flex justify-content-between">

                <a href="{{ route('departamentos.index') }}"
                    class="btn btn-secondary">
                    Cancelar
                </a>

                <button class="btn btn-primary-custom">
                    Actualizar departamento
                </button>

            </div>

        </form>

    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const codigo = document.getElementById("codigoDepto")
    const nombre = document.getElementById("nombreDepto")
    const descripcion = document.getElementById("descripcionDepto")

    codigo.addEventListener("keypress", function(e){

        if(!/[0-9]/.test(e.key)){
            e.preventDefault()
        }

    })

    codigo.addEventListener("paste", function(e){

        const texto = (e.clipboardData || window.clipboardData).getData('text')

        if(!/^\d+$/.test(texto)){
            e.preventDefault()
        }

    })

    function validarTexto(input){

        input.addEventListener("keypress", function(e){

            const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]$/

            if(!regex.test(e.key)){
                e.preventDefault()
            }

        })

    }

    validarTexto(nombre)
    validarTexto(descripcion)

})

</script>

@endsection