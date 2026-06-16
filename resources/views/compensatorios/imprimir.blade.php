
<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>

Solicitud #{{ $solicitud->id }}

</title>

<style>

@page{
    size: letter;
    margin:20px 35px;
}

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial, sans-serif;
    color:#111;
    font-size:11px;
}

.documento{
    border:1px solid #cfcfcf;
    border-radius:10px;
    padding:18px 22px;
}


/*=========================
ENCABEZADO
=========================*/

.header{
    position:relative;
    border-bottom:2px solid #1f3a56;
    padding-bottom:12px;
    margin-bottom:15px;
    min-height:90px;
}

.logo-left{
    position:absolute;
    left:0;
    top:0;
}

.logo-right{
    position:absolute;
    right:0;
    top:0;
}

.logo-left img,
.logo-right img{
    width:58px;
    height:58px;
    object-fit:contain;
}

.header-content{
    text-align:center;
    width:100%;
}

.municipio{
    font-size:14px;
    font-weight:bold;
}

.sub{
    font-size:8px;
    line-height:1.3;
    margin-top:3px;
}

.titulo{
    font-size:21px;
    font-weight:bold;
    margin-top:8px;
}

.subtitulo{
    font-size:10px;
    font-weight:bold;
}


/*=========================
CAJA DE ESTADO
=========================*/

.clasificacion{
    background:#eef3f8;
    border-left:6px solid #1f3a56;
    border-radius:8px;
    padding:8px 12px;
    margin-bottom:15px;
}

.tipo-clasificacion{
    margin-top:5px;
    font-size:14px;
    font-weight:bold;
    color:#1f3a56;
}


/*=========================
DATOS
=========================*/

.section-title{
    background:#1f3a56;
    color:white;
    padding:8px 10px;
    border-radius:6px;
    margin-top:15px;
    margin-bottom:10px;
    font-weight:bold;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th{
    background:#f3f3f3;
}

table th,
table td{
    border:1px solid #ccc;
    padding:8px;
    text-align:left;
}

.obs-line{
    border:1px solid #ccc;
    border-radius:8px;
    padding:12px;
    line-height:1.5;
    background:#fafafa;
    min-height:55px;
}


/*=========================
FIRMAS
=========================*/

.signatures{


margin-top:50px;

display:flex;

justify-content:space-around;

page-break-inside:avoid;


}


.firma-box{

width:260px;

text-align:center;

page-break-inside:avoid;


}



.firma-line{
    border-top:1px solid black;
    margin-bottom:7px;
}

.footer{
    margin-top:45px;
    display:flex;
    justify-content:space-between;
    font-size:9px;
    color:#555;
}

</style>

</head>
<body>

@php


$fechaTrabajada =
    \Carbon\Carbon::parse(
        $solicitud->fecha_trabajada
    );

$fechaCreacion =
    \Carbon\Carbon::parse(
        $solicitud->created_at
    );


@endphp

<div class="documento">


<div class="header">

    <div class="logo-left">

        <img src="{{ asset('img/logomuni.png') }}">

    </div>

    <div class="logo-right">

        <img src="{{ asset('img/logoescudo.png') }}">

    </div>

    <div class="header-content">

        <div class="municipio">

            MUNICIPALIDAD DE DANLÍ

        </div>

        <div class="sub">

            Departamento de El Paraíso<br>
            HONDURAS C.A.<br>
            Tel. 2763-2290, 2763-2080 Fax (504) 2763-2638

        </div>

        <div class="titulo">

            SOLICITUD

        </div>

        <div class="subtitulo">

            TRABAJO EN DÍA NO LABORAL

        </div>

    </div>

</div>


<div class="clasificacion">

    <div class="clasificacion">

<table style="border:none;width:100%;">

    <tr style="border:none;">

        <td style="border:none;width:50%;vertical-align:top;">

        <strong>

        ESTADO DE LA SOLICITUD

        </strong>

        <div class="tipo-clasificacion">

        {{ strtoupper($solicitud->estado) }}

        </div>

        </td>

        <td style="border:none;text-align:right;vertical-align:top;">

        <strong>

        TIPO DE PERMISO

        </strong>

        <div class="tipo-clasificacion">

        TRABAJO DÍA NO LABORAL

        </div>

        </td>

    </tr>

</table>

</div>


</div>



<div class="section-title">

    INFORMACIÓN GENERAL

</div>


<table>

    <tbody>

        <tr>

            <th width="25%">

                Solicitud No.

            </th>

            <td>

                {{ $solicitud->id }}

            </td>

        </tr>


        <tr>

            <th>

                Departamento

            </th>

            <td>

                {{ $solicitud->departamento->nombre ?? '—' }}

            </td>

        </tr>


        <tr>

            <th>

                Fecha trabajada

            </th>

            <td>

                {{ $fechaTrabajada
                    ->locale('es')
                    ->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}

            </td>

        </tr>


        <tr>

            <th>

                Fecha de solicitud

            </th>

            <td>

                {{ $fechaCreacion
                    ->locale('es')
                    ->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}

            </td>

        </tr>


        <tr>

            <th>

                Empleados incluidos

            </th>

            <td>

                {{ $solicitud->empleados->count() }}

            </td>

        </tr>

    </tbody>

</table>




<div class="section-title">

DESCRIPCIÓN DE LAS ACTIVIDADES REALIZADAS

</div>

<div class="obs-line">

{{ $solicitud->descripcion ?: 'Sin descripción registrada.' }}

</div>

<div class="section-title">

JUSTIFICACIÓN

</div>

<div class="obs-line">

{{ $solicitud->justificacion ?: 'Sin justificación registrada.' }}

</div>

<div class="section-title">

EMPLEADOS INCLUIDOS EN LA SOLICITUD

</div>

<table>

<thead>

    <tr>

        <th width="8%">
            #
        </th>

        <th width="67%">
            Nombre del empleado
        </th>

        <th width="25%">
            DNI
        </th>

    </tr>

</thead>

<tbody>

    @foreach($solicitud->empleados as $i => $emp)

        <tr>

            <td>

                {{ $i + 1 }}

            </td>

            <td>

                {{ trim(
                    ($emp->empleado->primer_nombre ?? '') . ' ' .
                    ($emp->empleado->segundo_nombre ?? '') . ' ' .
                    ($emp->empleado->primer_apellido ?? '') . ' ' .
                    ($emp->empleado->segundo_apellido ?? '')
                ) }}

            </td>

            <td>

                {{ $emp->dni_empleado }}

            </td>

        </tr>

    @endforeach

</tbody>


</table>


<div style="height:60px;"></div>

<div class="signatures">


<div class="firma-box">

    <div class="firma-line"></div>

    <strong>

        V.B. JEFE DE DEPARTAMENTO

    </strong>

</div>


<div class="firma-box">

    <div class="firma-line"></div>

    <strong>

        JEFE DE RECURSOS HUMANOS

    </strong>

</div>


</div>

<div class="footer">


<div>

    <strong>

        FECHA DE IMPRESIÓN:

    </strong>

    {{ now()
        ->locale('es')
        ->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}

</div>


<div>

    SISTEMA DE PERSONAL DE RRHH - SIPERH

</div>


</div>

</div>

<script>

    window.print();

</script>

</body>

</html>
