@extends('layouts.master')

@section('title', 'Registrar Usuario')

@section('content')

<div class="glass-card overflow-hidden">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center px-4 py-3"
         style="background: #27496d;">

        <h4 class="text-white fw-bold mb-0">
            Registrar Usuario
        </h4>

        <a href="{{ route('usuarios.index') }}"
           class="btn btn-primary-custom">
            Volver
        </a>

    </div>

    <!-- CONTENIDO -->
    <div class="p-4">

        @if ($errors->any())

            <div class="alert alert-danger">
                Hay errores en el formulario. Revisa los campos.
            </div>

        @endif

        <form method="POST"
              action="{{ route('usuarios.store') }}">

            @csrf

            <div class="row">

                <!-- EMPLEADO -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Empleado
                    </label>

                    <select name="empleado_dni"
                            id="empleadoSelect"
                            class="form-select @error('empleado_dni') is-invalid @enderror">

                        <option value="">
                            Seleccione un empleado
                        </option>

                        @foreach($empleados as $empleado)

                            <option value="{{ $empleado->DNI }}"
                                    data-nombre="{{ $empleado->primer_nombre }} {{ $empleado->primer_apellido }}"
                                    {{ old('empleado_dni') == $empleado->DNI ? 'selected' : '' }}>

                                {{ $empleado->DNI }}
                                -
                                {{ $empleado->primer_nombre }}
                                {{ $empleado->primer_apellido }}

                            </option>

                        @endforeach

                    </select>

                    @error('empleado_dni')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- NOMBRE -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Empleado seleccionado
                    </label>

                    <input type="text"
                           name="name"
                           id="nameInput"
                           value="{{ old('name') }}" 
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Nombre del usuario" readonly>

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- USERNAME -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Usuario
                    </label>

                    <input type="text"
                           name="username"
                           id="username"
                           value="{{ old('username') }}"
                           class="form-control @error('username') is-invalid @enderror"
                           placeholder="Ej. jmedina">

                    <small class="text-muted">
                        Sin espacios. Puede usar letras, números, punto, guion y guion bajo.
                    </small>

                    @error('username')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- CORREO -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Correo
                    </label>

                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror"
                           placeholder="Opcional">

                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- TELÉFONO -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Teléfono
                    </label>

                    <input type="text"
                           name="telefono"
                           id="telefono"
                           maxlength="8"
                           value="{{ old('telefono') }}"
                           class="form-control @error('telefono') is-invalid @enderror"
                           placeholder="Ej. 98765432">

                    @error('telefono')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- ROL -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Rol
                    </label>

                    <select name="rol"
                            class="form-select @error('rol') is-invalid @enderror">

                        <option value="">
                            Seleccione...
                        </option>

                        @if(auth()->user()->rol === 'superadmin')

                            <option value="superadmin">
                                Super administrador
                            </option>

                            <option value="rrhh">
                                Administrador RRHH
                            </option>

                        @endif

                        <option value="jefe_departamento">
                            Jefe de departamento
                        </option>

                    </select>

                    @error('rol')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- PASSWORD -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Contraseña
                    </label>

                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control @error('password') is-invalid @enderror">

                    <small class="text-muted">
                        Mínimo 8 caracteres, mayúscula, número y símbolo.
                    </small>

                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

                <!-- CONFIRMAR -->
                <div class="col-md-6 mb-3">

                    <label class="form-label fw-bold">
                        Confirmar contraseña
                    </label>

                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control">

                    <small id="passwordMatch"
                           class="text-muted">
                    </small>

                </div>

            </div>

            <!-- BOTÓN -->
            <div class="d-flex justify-content-end mt-4">

                <button type="submit"
                        class="btn btn-primary-custom">

                    Guardar Usuario

                </button>

            </div>

        </form>

    </div>

</div>

<script>

/* ==========================
   AUTOLLENAR NOMBRE
========================== */

document.getElementById('empleadoSelect')
.addEventListener('change', function() {

    const option = this.options[this.selectedIndex];
    const nombre = option.dataset.nombre;

    document.getElementById('nameInput').value = nombre || '';

});

/* ==========================
   VALIDAR USERNAME
========================== */

document.getElementById('username')
.addEventListener('input', function() {

    this.value = this.value
        .toLowerCase()
        .replace(/\s/g, '');

});

/* ==========================
   VALIDAR TELÉFONO
========================== */

document.getElementById('telefono')
.addEventListener('input', function() {

    this.value = this.value.replace(/[^0-9]/g, '');

});

/* ==========================
   COMPARAR PASSWORDS
========================== */

const password = document.getElementById('password');
const confirmPassword = document.getElementById('password_confirmation');
const passwordMatch = document.getElementById('passwordMatch');

function validarPasswords() {

    if(confirmPassword.value === '') {

        passwordMatch.textContent = '';
        return;

    }

    if(password.value === confirmPassword.value) {

        passwordMatch.textContent = 'Las contraseñas coinciden.';
        passwordMatch.className = 'text-success';

    } else {

        passwordMatch.textContent = 'Las contraseñas no coinciden.';
        passwordMatch.className = 'text-danger';

    }

}

password.addEventListener('input', validarPasswords);
confirmPassword.addEventListener('input', validarPasswords);

</script>

@endsection