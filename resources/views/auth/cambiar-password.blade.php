@extends('layouts.auth')

@section('title', 'Cambiar Contraseña')

@section('content')

<div class="login-card">

    <img src="/images/logo.png"
         class="logo"
         alt="Logo">

    <div class="login-title mb-2">
        Cambiar Contraseña
    </div>

    <p class="login-text">
        Por seguridad, debes actualizar tu contraseña temporal.
    </p>

    @if ($errors->any())

        <div class="alert alert-danger text-start">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <form method="POST"
          action="{{ route('password.actualizar') }}">

        @csrf

        <!-- PASSWORD -->
        <div class="mb-3 text-start">

            <label class="form-label fw-bold">
                Nueva contraseña
            </label>

            <input type="password"
                   name="password"
                   id="password"
                   class="form-control"
                   required>

            <small class="text-muted">
                Debe contener mínimo 8 caracteres,
                una mayúscula, un número y un símbolo.
            </small>

        </div>

        <!-- CONFIRMAR -->
        <div class="mb-3 text-start">

            <label class="form-label fw-bold">
                Confirmar contraseña
            </label>

            <input type="password"
                   name="password_confirmation"
                   id="password_confirmation"
                   class="form-control"
                   required>

            <small id="passwordMatch"></small>

        </div>

        <button type="submit"
                class="btn-login mt-2">

            Actualizar Contraseña

        </button>

    </form>

</div>

<script>

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