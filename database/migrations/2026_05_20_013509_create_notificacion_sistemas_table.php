<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up(): void
    {
        Schema::create('notificaciones_sistema', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('rol_destino', 50)->nullable();

            $table->string('titulo', 150);
            $table->text('mensaje');

            $table->string('tipo', 50)->default('info');
            $table->string('modulo', 80)->nullable();

            $table->string('url')->nullable();

            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();

            $table->timestamps();

            $table->foreign('usuario_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion_sistemas');
    }
};
