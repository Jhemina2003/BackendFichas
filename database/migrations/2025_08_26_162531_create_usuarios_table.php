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
        Schema::create('usuarios', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('usuario_id');
            $table->integer('fk_dominio_tipo_servicio_id');
            $table->integer('fk_sucursal_id');
            $table->integer('fk_persona_id');
            $table->foreign('fk_sucursal_id')->references('sucursal_id')->on('sucursales');
            $table->foreign('fk_dominio_tipo_servicio_id')->references('dominio_id')->on('dominios');

            /**
             * Columnas
             */
            $table->string('usuario', 255);
            $table->string('nombre_completo', 255);
            $table->string('correo_electronico', 255);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
