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
        Schema::create('asignaciones', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('asignacion_id');
            $table->integer('fk_usuario_id');
            $table->integer('fk_ventanilla_id');
            $table->foreign('fk_usuario_id')->references('usuario_id')->on('usuarios');
            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');

            /**
             * Columnas
             */
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
