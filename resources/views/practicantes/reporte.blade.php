<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reporte Practicante</title>

<style>

@page{
    margin:120px 35px 70px 35px;
}

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:11px;
    color:#111;
}

header{
    position: fixed;
    top:-105px;
    left:0;
    right:0;
}

footer{
    position: fixed;
    bottom:-45px;
    left:0;
    right:0;
    text-align:center;
    font-size:10px;
    color:#555;
}

.encabezado{
    width:100%;
    border-collapse: collapse;
}

.encabezado td{
    border:1px solid #555;
    padding:6px;
}

.logo{
    width:12%;
    text-align:center;
}

.logo img{
    height:65px;
}

.titulo{
    width:76%;
    text-align:center;
    line-height:1.4;
}

.nombre-municipalidad{
    font-size:12px;
    font-weight:bold;
}

.subtitulo{
    font-size:10px;
}

.titulo-reporte{
    margin-top:4px;
    font-size:14px;
    font-weight:bold;
}

.tarjeta{
    border:1px solid #555;
    background:#f7f7f7;
    padding:12px;
    margin-bottom:15px;
    line-height:1.8;
}

.section-title{
    background:#e9ecef;
    border:1px solid #555;
    padding:7px;
    font-weight:bold;
    margin-bottom:0;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-bottom:15px;
}

th,td{
    border:1px solid #555;
    padding:8px;
}

th{
    background:#f1f1f1;
    width:30%;
    text-align:left;
}

.estado-activo{
    color:green;
    font-weight:bold;
}

.estado-inactivo{
    color:red;
    font-weight:bold;
}

.observacion{
    border:1px solid #555;
    padding:10px;
    line-height:1.8;
}

.logo{
    width:12%;
    text-align:center;
    vertical-align:middle;
}

.logo img{
    width:60px;
    height:auto;
}

</style>

</head>

<body>

<header>

<table class="encabezado">

<tr>

<td class="logo">
    <img src="{{ public_path('img/logoescudo.png') }}">
</td>

<td class="titulo">

<div class="nombre-municipalidad">
MUNICIPALIDAD DE DANLÍ
</div>

<div class="subtitulo">
Departamento de Personal
</div>

<div class="subtitulo">
Tel. 2763-2280, 2763-2080
</div>

<div class="titulo-reporte">
DATOS DEL PRACTICANTE
</div>

</td>

<td class="logo">
    <img src="{{ public_path('img/logomuni.png') }}">
</td>

</tr>

</table>

</header>

<footer>

Generado el {{ $fechaGeneracion }}

</footer>


<div class="tarjeta">

<strong>Nombre completo:</strong>

{{ $practicante->nombre_completo }}

<br>

<strong>DNI:</strong>

{{ $practicante->dni_practicante }}

</div>


<div class="section-title">

Información General

</div>

<table>

<tbody>

<tr>
<th>Institución</th>
<td>{{ $practicante->institucion }}</td>
</tr>

<tr>
<th>Correo electrónico</th>
<td>{{ $practicante->correo ?: 'No registrado' }}</td>
</tr>

<tr>
<th>Horas requeridas</th>
<td>{{ $practicante->horas_requeridas ?: 0 }} horas</td>
</tr>

<tr>
<th>Fecha de inicio</th>
<td>

{{ \Carbon\Carbon::parse($practicante->fecha_inicio)
->locale('es')
->translatedFormat('d \d\e F \d\e\l Y') }}

</td>
</tr>

<tr>
<th>Fecha de finalización</th>
<td>

@if($practicante->fecha_fin)

{{ \Carbon\Carbon::parse($practicante->fecha_fin)
->locale('es')
->translatedFormat('d \d\e F \d\e\l Y') }}

@else

No definida

@endif

</td>
</tr>

<tr>
<th>Departamento asignado</th>
<td>

{{ $practicante->departamento->nombre ?? 'Sin asignar' }}

</td>
</tr>

<tr>
<th>Estado</th>
<td>

@if($practicante->activo)

<span class="estado-activo">
ACTIVO
</span>

@else

<span class="estado-inactivo">
INACTIVO
</span>

@endif

</td>
</tr>

</tbody>

</table>


<div class="section-title">

Observaciones

</div>

<div class="observacion">

Este documento certifica la información registrada del practicante en el Sistema de Personal de Recursos Humanos (SIPERH) de la Municipalidad de Danlí.

</div>


<script type="text/php">

if (isset($pdf)) {

    $font = $fontMetrics->get_font(
        "DejaVu Sans",
        "normal"
    );

    $size = 9;

    $footerText =
        "Página {PAGE_NUM}/{PAGE_COUNT}";

    $textWidth =
        $fontMetrics->get_text_width(
            $footerText,
            $font,
            $size
        );

    $x =
        ($pdf->get_width() - $textWidth) / 2;

    $y =
        $pdf->get_height() - 18;

    $pdf->page_text(
        $x,
        $y,
        $footerText,
        $font,
        $size
    );

}

</script>

</body>
</html>