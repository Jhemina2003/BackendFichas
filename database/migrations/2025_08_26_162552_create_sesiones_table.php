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
        Schema::create('sesiones', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('sesion_id');
            $table->integer('fk_sucursal_id');
            $table->integer('fk_dominio_estado_id');
            $table->foreign('fk_sucursal_id')->references('sucursal_id')->on('sucursales');
            $table->foreign('fk_dominio_estado_id')->references('dominio_id')->on('dominios');

            /**
             * Columnas
             */
            $table->timestamp('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
