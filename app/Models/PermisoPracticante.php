<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoPracticante extends Model
{
    protected $table = 'permisos_practicantes';

    protected $fillable = [

        'practicante_id',

        'tipo_permiso_id',

        'estado_permiso_id',

        'modalidad',

        'fecha_inicio',

        'fecha_fin',

        'horas',

        'hora_salida',

        'hora_entrada',

        'motivo',

        'motivo_rechazo',

        'documento',
    ];

    protected $casts = [

        'fecha_inicio' => 'date',

        'fecha_fin' => 'date',
    ];

    public function practicante()
    {
        return $this->belongsTo(
            Practicante::class,
            'practicante_id'
        );
    }

    public function tipo()
    {
        return $this->belongsTo(
            TipoPermisoPracticante::class,
            'tipo_permiso_id'
        );
    }

    public function estado()
    {
        return $this->belongsTo(
            EstadoPermisoSistema::class,
            'estado_permiso_id'
        );
    }
}