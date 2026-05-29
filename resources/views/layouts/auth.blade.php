<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'SIPERH | Login')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        .login-bg {
            position: relative;
            width: 100%;
            height: 100vh;
            background-size: cover;
            background-position: center;
            transition: background-image 0.2s ease-in-out;
        }

        .login-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(20, 40, 70, 0.25);
        }

        .login-card {
            position: absolute;
            top: 50%;
            left: 8%;
            transform: translateY(-50%);
            width: 420px;
            background: rgba(255, 255, 255, 0.80);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            padding: 35px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.40);
            box-shadow: 0 30px 60px rgba(0,0,0,0.25);
            animation: fadeInUp 0.6s ease;
            z-index: 2;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(-40%) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) scale(1);
            }
        }

        .logo {
            width: 130px;
            margin-bottom: 12px;
        }

        .login-title {
            font-weight: 700;
            color: #2c4a6b;
            font-size: 25px;
            margin-bottom: 0;
        }

        .login-subtitle {
            font-size: 14px;
            color: #2c4a6b;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .login-text {
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }

        .attempts-text {
            font-size: 13px;
            color: #555;
            margin-top: 14px;
            margin-bottom: 12px;
        }

        .form-control {
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            border-color: #ced4da !important;
            box-shadow: none !important;
        }

        .form-control:focus {
            border-color: #9bb8d3 !important;
            box-shadow: 0 0 0 0.15rem rgba(44, 74, 107, 0.15) !important;
        }

        .field-valid,
        .field-invalid {
            border-color: #ced4da !important;
            box-shadow: none !important;
        }

        .error-text {
            font-size: 12px;
            color: #dc3545;
            margin-top: -8px;
            margin-bottom: 10px;
        }

        .alert-login {
            font-size: 13px;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 14px;
            color: #2c4a6b;
        }

        .btn-login {
            background: #2c4a6b;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }

        .btn-login:hover {
            background: #1f3a56;
            color: white;
        }

        .btn-login:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .link {
            font-size: 13px;
            color: #2c4a6b;
            text-decoration: none;
            cursor: pointer;
        }

        .link:hover {
            text-decoration: underline;
        }

        .info-box {
            background: rgba(0,0,0,0.05);
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            color: #333;
            transition: all 0.2s ease-in-out;
        }

        .info-box:hover {
            background: rgba(44, 74, 107, 0.10);
        }

        .login-footer {
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-top: 18px;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.45);
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        }

        .modal-header {
            background: linear-gradient(135deg, #1f3a56, #2d4f73);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .btn-modal {
            background: #2c4a6b;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 9px 18px;
        }

        .btn-modal:hover {
            background: #1f3a56;
            color: white;
        }

        .system-info {
            font-size: 13px;
            color: #555;
            background: rgba(44, 74, 107, 0.08);
            border-radius: 10px;
            padding: 12px;
            margin-top: 14px;
        }
    </style>
</head>

<body>

<div class="login-bg">
    @yield('content')
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    if (!input) return;

    input.type = input.type === 'password' ? 'text' : 'password';
}

document.addEventListener("DOMContentLoaded", function(){

    const usuario = document.getElementById('usuario');
    const password = document.getElementById('password');
    const btn = document.getElementById('btnLogin');

    const errorUsuario = document.getElementById('errorUsuario');
    const errorPassword = document.getElementById('errorPassword');

    if (!usuario || !password || !btn || !errorUsuario || !errorPassword) return;

    function validar(input, errorEl, min, mensaje) {
        if(input.value.trim() === ""){
            errorEl.textContent = "Campo obligatorio";
            return false;
        }

        if(input.value.length < min){
            errorEl.textContent = mensaje;
            return false;
        }

        errorEl.textContent = "";
        return true;
    }

    function validarForm(){
        const u = validar(usuario, errorUsuario, 3, "Mínimo 3 caracteres");
        const p = validar(password, errorPassword, 4, "Mínimo 4 caracteres");
        btn.disabled = !(u && p);
    }

    usuario.addEventListener('input', validarForm);
    password.addEventListener('input', validarForm);

    usuario.addEventListener('keypress', e => {
        if(e.key === ' ') e.preventDefault();
    });
});
</script>

<script>
const imagenes = [
    "/images/login-1.png",
    "/images/login-2.png"
];

let index = localStorage.getItem("bgIndex");

if(index === null){
    index = 0;
} else {
    index = parseInt(index) === 0 ? 1 : 0;
}

localStorage.setItem("bgIndex", index);

const loginBg = document.querySelector('.login-bg');

if (loginBg) {
    loginBg.style.backgroundImage = `url(${imagenes[index]})`;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>