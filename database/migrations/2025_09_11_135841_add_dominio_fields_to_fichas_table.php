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
        Schema::table('fichas', function (Blueprint $table) {
            // Agregar campos para dominios
            $table->integer('fk_tipo_ficha_id')->nullable()->after('ficha_id');
            $table->integer('fk_tipo_servicio_id')->nullable()->after('fk_tipo_ficha_id');
            $table->integer('fk_prioridad_ficha_id')->nullable()->after('fk_tipo_servicio_id');
            
            // Agregar foreign keys
            $table->foreign('fk_tipo_ficha_id')->references('dominio_id')->on('dominios');
            $table->foreign('fk_tipo_servicio_id')->references('dominio_id')->on('dominios');
            $table->foreign('fk_prioridad_ficha_id')->references('dominio_id')->on('dominios');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            // Eliminar foreign keys primero
            $table->dropForeign(['fk_tipo_ficha_id']);
            $table->dropForeign(['fk_tipo_servicio_id']);
            $table->dropForeign(['fk_prioridad_ficha_id']);
            
            // Eliminar columnas
            $table->dropColumn(['fk_tipo_ficha_id', 'fk_tipo_servicio_id', 'fk_prioridad_ficha_id']);
        });
    }
};
