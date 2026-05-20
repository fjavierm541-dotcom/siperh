<?php

namespace App\Helpers;

use App\Models\NotificacionSistema;

class NotificacionHelper
{
    public static function crear(
        ?int $usuarioId,
        ?string $rolDestino,
        string $titulo,
        string $mensaje,
        string $tipo = 'info',
        ?string $modulo = null,
        ?string $url = null
    ): void {
        NotificacionSistema::create([
            'usuario_id' => $usuarioId,
            'rol_destino' => $rolDestino,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'modulo' => $modulo,
            'url' => $url,
            'leida' => false,
        ]);
    }
}