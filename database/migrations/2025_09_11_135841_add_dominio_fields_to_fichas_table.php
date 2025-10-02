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
            // Mantener solo tipo_ficha si es necesario, eliminar tipo_servicio
            $table->integer('fk_tipo_ficha_id')->nullable()->after('ficha_id');
            $table->foreign('fk_tipo_ficha_id')->references('dominio_id')->on('dominios');
            // El campo fk_servicio_id debe estar en la migración principal de fichas si no existe
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            // Eliminar foreign key y columna de tipo_ficha
            $table->dropForeign(['fk_tipo_ficha_id']);
            $table->dropColumn(['fk_tipo_ficha_id']);
            // El campo fk_servicio_id debe eliminarse en la migración correspondiente si es necesario
        });
    }
};
