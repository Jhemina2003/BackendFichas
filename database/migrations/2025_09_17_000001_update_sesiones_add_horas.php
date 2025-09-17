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
        Schema::table('sesiones', function (Blueprint $table) {
            // Cambiar fecha a datetime si es necesario
            if (Schema::hasColumn('sesiones', 'fecha')) {
                $table->dateTime('fecha')->change();
            }
            // Agregar hora_inicio y hora_cierre si no existen
            if (!Schema::hasColumn('sesiones', 'hora_inicio')) {
                $table->dateTime('hora_inicio')->nullable()->after('fecha');
            }
            if (!Schema::hasColumn('sesiones', 'hora_cierre')) {
                $table->dateTime('hora_cierre')->nullable()->after('hora_inicio');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesiones', function (Blueprint $table) {
            if (Schema::hasColumn('sesiones', 'hora_inicio')) {
                $table->dropColumn('hora_inicio');
            }
            if (Schema::hasColumn('sesiones', 'hora_cierre')) {
                $table->dropColumn('hora_cierre');
            }
            // Si quieres revertir fecha a date, descomenta la siguiente línea:
            // $table->date('fecha')->change();
        });
    }
};
