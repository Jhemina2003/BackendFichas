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
        Schema::table('usuarios', function (Blueprint $table) {
            // Elimina la columna y la foreign key si existe
            if (Schema::hasColumn('usuarios', 'fk_dominio_tipo_servicio_id')) {
                $table->dropForeign(['fk_dominio_tipo_servicio_id']);
                $table->dropColumn('fk_dominio_tipo_servicio_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se recrea la columna
    }
};
