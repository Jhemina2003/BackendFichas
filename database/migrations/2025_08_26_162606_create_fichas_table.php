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
        Schema::create('fichas', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('ficha_id');
            $table->integer('fk_sesion_id');
            $table->integer('fk_dominio_tipo_id');
            $table->integer('fk_dominio_prioridad_id');
            $table->integer('fk_llamada_id');
            $table->foreign('fk_sesion_id')->references('sesion_id')->on('sesiones');
            $table->foreign('fk_dominio_tipo_id')->references('dominio_id')->on('dominios');
            $table->foreign('fk_dominio_prioridad_id')->references('dominio_id')->on('dominios');

            /**
             * Columnas
             */
            $table->integer('numero');
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->integer('cantidad_llamadas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichas');
    }
};
