<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_permiso_practicantes', function (Blueprint $table) {

            $table->id();

            $table->string('nombre');

            $table->boolean('requiere_documento')
                ->default(false);

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();
        });

        DB::table('tipos_permiso_practicantes')->insert([

            [
                'nombre' => 'Permiso Particular/Personal',
                'requiere_documento' => 0,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'Cita Médica',
                'requiere_documento' => 0,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'Incapacidad',
                'requiere_documento' => 1,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'Permiso Fúnebre',
                'requiere_documento' => 0,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'nombre' => 'Gestiones Académicas',
                'requiere_documento' => 0,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_permiso_practicantes');
    }
};