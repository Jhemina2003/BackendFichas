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
        Schema::create('seguimientos', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('seguimiento_id');
            $table->integer('fk_ficha_id');
            $table->integer('fk_ventanilla_id')->nullable();
            $table->integer('fk_usuario_id')->nullable();
            $table->integer('fk_dominio_estado_id');
            $table->integer('fk_dominio_accion_id');
            $table->foreign('fk_ficha_id')->references('ficha_id')->on('fichas');
            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');
            $table->foreign('fk_usuario_id')->references('usuario_id')->on('usuarios');
            $table->foreign('fk_dominio_estado_id')->references('dominio_id')->on('dominios');
            $table->foreign('fk_dominio_accion_id')->references('dominio_id')->on('dominios');

            /**
             * Columnas
             */
            $table->timestamp('fecha')->useCurrent();
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
