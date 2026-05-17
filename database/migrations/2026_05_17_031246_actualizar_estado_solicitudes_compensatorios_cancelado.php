<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE solicitudes_compensatorios 
            MODIFY estado ENUM('pendiente', 'aprobado', 'rechazado', 'cancelado') 
            DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE solicitudes_compensatorios 
            MODIFY estado ENUM('pendiente', 'aprobado', 'rechazado') 
            DEFAULT 'pendiente'");
    }
};