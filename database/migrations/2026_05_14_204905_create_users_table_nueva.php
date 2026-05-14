<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Relación opcional con empleados
            $table->string('empleado_dni', 255)->nullable();

            // Datos del usuario
            $table->string('name', 150);
            $table->string('username', 50)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('telefono', 30)->nullable();

            // Acceso
            $table->string('password');
            $table->enum('rol', [
                'superadmin',
                'rrhh',
                'jefe_departamento'
            ])->default('jefe_departamento');

            $table->boolean('activo')->default(true);

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('empleado_dni')
                ->references('dni')
                ->on('empleados')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};