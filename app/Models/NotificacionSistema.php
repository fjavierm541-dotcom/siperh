<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionSistema extends Model
{
    protected $table = 'notificaciones_sistema';

    protected $fillable = [
        'usuario_id',
        'rol_destino',
        'titulo',
        'mensaje',
        'tipo',
        'modulo',
        'url',
        'leida',
        'leida_en',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_en' => 'datetime',
    ];
}