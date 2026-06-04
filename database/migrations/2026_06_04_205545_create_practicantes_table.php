<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practicantes', function (Blueprint $table) {

            $table->id();

            $table->string('nombre_completo');

            $table->string('dni_practicante', 20)->nullable();

            $table->string('institucion');

            $table->string('correo')->nullable();

            $table->integer('horas_requeridas')->nullable();

            $table->date('fecha_inicio');

            $table->date('fecha_fin')->nullable();

            $table->unsignedBigInteger('departamento_id');

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('departamento_id')
                ->references('id')
                ->on('departamentos_muni')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practicantes');
    }
};