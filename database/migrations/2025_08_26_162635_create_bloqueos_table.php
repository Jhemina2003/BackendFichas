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
        Schema::create('bloqueos', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('bloqueo_id');
            $table->integer('fk_ventanilla_id')->nullable();
            $table->integer('fk_horario_id')->nullable();
            $table->integer('fk_usuario_id');
            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');
            $table->foreign('fk_horario_id')->references('horario_id')->on('horarios');
            $table->foreign('fk_usuario_id')->references('usuario_id')->on('usuarios');

            /**
             * Columnas
             */
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->string('motivo', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloqueos');
    }
};
