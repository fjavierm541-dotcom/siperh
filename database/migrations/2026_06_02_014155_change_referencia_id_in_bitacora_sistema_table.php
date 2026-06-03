<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE bitacora_sistema
            MODIFY referencia_id VARCHAR(100) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE bitacora_sistema
            MODIFY referencia_id BIGINT UNSIGNED NULL
        ");
    }
};