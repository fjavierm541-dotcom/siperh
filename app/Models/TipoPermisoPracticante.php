<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoPermisoPracticante extends Model
{
    protected $table = 'tipos_permiso_practicantes';

    protected $fillable = [
        'nombre',
        'requiere_documento',
        'activo',
    ];

    public function permisos()
    {
        return $this->hasMany(
            PermisoPracticante::class,
            'tipo_permiso_id'
        );
    }
}