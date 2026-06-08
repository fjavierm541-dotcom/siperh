<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        @yield('code') | SIPERH
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>

        body{
            margin:0;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            overflow:hidden;

            font-family:'Segoe UI', sans-serif;

            background:
                linear-gradient(rgba(15,30,50,.82), rgba(15,30,50,.82)),
                url('/images/login-1.png');

            background-size:cover;
            background-position:center;
        }

        .error-container{
            width:100%;
            max-width:700px;
            padding:25px;
        }

        .error-card{

            background:rgba(255,255,255,.88);

            backdrop-filter:blur(14px);
            -webkit-backdrop-filter:blur(14px);

            border-radius:22px;

            padding:45px;

            box-shadow:
                0 25px 60px rgba(0,0,0,.28);

            border:1px solid rgba(255,255,255,.4);

            text-align:center;

            animation:fadeIn .35s ease;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(10px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .logo{
            width:75px;
            margin-bottom:12px;
        }

        .system-name{
            font-size:18px;
            font-weight:700;
            color:#1f3a56;
            margin-bottom:2px;
        }

        .system-subtitle{
            font-size:13px;
            color:#5c6b7a;
            margin-bottom:28px;
        }

        .error-code{
            font-size:95px;
            font-weight:800;
            color:#1f3a56;
            line-height:1;
            margin-bottom:12px;
        }

        .error-title{
            font-size:38px;
            font-weight:700;
            color:#c89b4d;
            margin-bottom:22px;
        }

        .error-message{
            font-size:16px;
            color:#4a5565;
            line-height:1.7;
            margin-bottom:32px;
        }

        .btn-siper{

            background:#1f3a56;
            color:white;

            border:none;

            padding:12px 24px;

            border-radius:10px;

            font-weight:600;

            transition:.2s;
        }

        .btn-siper:hover{
            background:#162a40;
            color:white;
        }

        .btn-outline-siper{

            border:2px solid #1f3a56;
            color:#1f3a56;

            padding:10px 22px;

            border-radius:10px;

            font-weight:600;

            transition:.2s;
        }

        .btn-outline-siper:hover{
            background:#1f3a56;
            color:white;
        }

        .footer{
            margin-top:28px;
            font-size:13px;
            color:#7b8794;
        }

        @media(max-width:768px){

            .error-card{
                padding:35px 25px;
            }

            .error-code{
                font-size:70px;
            }

            .error-title{
                font-size:28px;
            }

        }

    </style>
</head>

<body>

<div class="error-container">

    <div class="error-card">

        <img src="/images/isologosiperh.png"
             class="logo"
             alt="SIPER">

        <div class="system-name">
            SIPERH
        </div>

        <div class="system-subtitle">
            Sistema de Personal de Recursos Humanos
        </div>

        <div class="error-code">
            @yield('code')
        </div>

        <div class="error-title">
            @yield('title')
        </div>

        <div class="error-message">
            @yield('message')
        </div>

        <div class="d-flex justify-content-center gap-3 flex-wrap">

            @yield('buttons')

        </div>

        <div class="footer">
            © 2026 SIPERH · Sistema de Personal de Recursos Humanos
        </div>

    </div>

</div>

</body>
</html>