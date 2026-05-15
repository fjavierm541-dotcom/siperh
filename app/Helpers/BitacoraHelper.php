<?php

namespace App\Helpers;

use App\Models\BitacoraSistema;

class BitacoraHelper
{
    public static function registrar(
        string $accion,
        string $modulo,
        string $descripcion,
        $referenciaId = null,
        $referenciaTipo = null,
        $valoresAnteriores = null,
        $valoresNuevos = null,
        string $estado = 'exitoso'
    ) {

        $usuario = auth()->user();

        BitacoraSistema::create([

            'usuario_id' => $usuario?->id,

            'usuario_nombre' => $usuario?->name ?? 'Sistema',

            'rol_usuario' => $usuario?->rol ?? 'sistema',

            'empleado_dni' => $usuario?->empleado_dni,

            'accion' => $accion,

            'modulo' => $modulo,

            'descripcion' => $descripcion,

            'ip_equipo' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'metodo' => request()->method(),

            'ruta' => request()->path(),

            'referencia_id' => $referenciaId,

            'referencia_tipo' => $referenciaTipo,

            'valores_anteriores' => $valoresAnteriores,

            'valores_nuevos' => $valoresNuevos,

            'estado' => $estado,

        ]);
    }
}