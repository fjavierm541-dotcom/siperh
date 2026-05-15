<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sistema', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('usuario_nombre', 150)->nullable();
            $table->string('rol_usuario', 50)->nullable();
            $table->string('empleado_dni', 30)->nullable();

            $table->string('accion', 100);
            $table->string('modulo', 100);

            $table->text('descripcion')->nullable();

            $table->string('ip_equipo', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->string('metodo', 10)->nullable();
            $table->string('ruta', 255)->nullable();

            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tipo', 100)->nullable();

            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();

            $table->string('estado', 30)->default('exitoso');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_sistema');
    }
};