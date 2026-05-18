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

            'user_agent' => substr(request()->userAgent() ?? '', 0, 1000),

            'metodo' => request()->method(),

            'ruta' => request()->path(),

            'referencia_id' => (string) $referenciaId,

            'referencia_tipo' => $referenciaTipo,

            'valores_anteriores' => $valoresAnteriores
                ? json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE)
                : null,

            'valores_nuevos' => $valoresNuevos
                ? json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE)
                : null,

            'estado' => $estado,

        ]);
    }
}