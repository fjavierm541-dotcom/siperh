<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_practicantes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('practicante_id')
                ->constrained('practicantes')
                ->cascadeOnDelete();

            $table->foreignId('tipo_permiso_id')
                ->constrained('tipos_permiso_practicantes');

            $table->foreignId('estado_permiso_id')
                ->constrained('estados_permiso_sistema');

            $table->enum('modalidad', [
                'horas',
                'medio_dia',
                'un_dia',
                'varios_dias'
            ]);

            $table->date('fecha_inicio');

            $table->date('fecha_fin')
                ->nullable();

            $table->decimal('horas', 8, 2)
                ->default(0);

            $table->time('hora_salida')
                ->nullable();

            $table->time('hora_entrada')
                ->nullable();

            $table->text('motivo')
                ->nullable();

            $table->string('documento')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_practicantes');
    }
};