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
            // Agregar fk_tipo_servicio_id si no existe
            if (!Schema::hasColumn('fichas', 'fk_tipo_servicio_id')) {
                $table->unsignedBigInteger('fk_tipo_servicio_id')->after('fk_tipo_ficha_id');
                $table->foreign('fk_tipo_servicio_id')->references('dominio_id')->on('dominios');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fichas', function (Blueprint $table) {
            // Restaurar fk_servicio_id
            if (!Schema::hasColumn('fichas', 'fk_servicio_id')) {
                $table->unsignedBigInteger('fk_servicio_id')->after('fk_tipo_ficha_id');
            }
            
            // Eliminar fk_tipo_servicio_id
            if (Schema::hasColumn('fichas', 'fk_tipo_servicio_id')) {
                $table->dropForeign(['fk_tipo_servicio_id']);
                $table->dropColumn('fk_tipo_servicio_id');
            }
        });
    }
};