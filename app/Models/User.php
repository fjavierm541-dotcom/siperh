<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'empleado_dni',
        'name',
        'username',
        'email',
        'telefono',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    /* ======================
       RELACIONES
    ====================== */

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_dni', 'dni');
    }
}