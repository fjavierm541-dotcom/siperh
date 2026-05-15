<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos_permisos_sistema', function (Blueprint $table) {
            $table->string('usuario_nombre', 150)
                  ->nullable()
                  ->after('permiso_id');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos_permisos_sistema', function (Blueprint $table) {
            $table->dropColumn('usuario_nombre');
        });
    }
};