<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BitacoraSistema extends Model
{
    protected $table = 'bitacora_sistema';

    protected $fillable = [
        'usuario_id',
        'usuario_nombre',
        'rol_usuario',
        'empleado_dni',
        'accion',
        'modulo',
        'descripcion',
        'ip_equipo',
        'user_agent',
        'metodo',
        'ruta',
        'referencia_id',
        'referencia_tipo',
        'valores_anteriores',
        'valores_nuevos',
        'estado',
    ];

    protected $casts = [
        'valores_anteriores' => 'array',
        'valores_nuevos' => 'array',
    ];
}