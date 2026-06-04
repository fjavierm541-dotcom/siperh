<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Practicante extends Model
{
    protected $table = 'practicantes';

    protected $fillable = [
        'nombre_completo',
        'dni_practicante',
        'institucion',
        'correo',
        'horas_requeridas',
        'fecha_inicio',
        'fecha_fin',
        'departamento_id',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function departamento()
    {
        return $this->belongsTo(
            DepartamentoMuni::class,
            'departamento_id'
        );
    }
}