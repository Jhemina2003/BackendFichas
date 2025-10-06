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
        // Primero eliminar las claves foráneas de fichas si existen
        Schema::table('fichas', function (Blueprint $table) {
            if (Schema::hasColumn('fichas', 'fk_servicio_id')) {
                $table->dropForeign(['fk_servicio_id']);
                $table->dropColumn('fk_servicio_id');
            }
        });
        
        // Eliminar tablas de servicios
        Schema::dropIfExists('servicio_ventanilla');
        Schema::dropIfExists('servicios');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recrear las tablas si es necesario
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('servicio_id');
            $table->string('nombre');
            $table->unsignedBigInteger('fk_ventanilla_id');
            $table->unsignedBigInteger('fk_dominio_tipo_servicio_id');
            $table->date('fecha');
            $table->timestamps();
        });

        Schema::create('servicio_ventanilla', function (Blueprint $table) {
            $table->id('servicio_ventanilla_id');
            $table->unsignedBigInteger('fk_ventanilla_id');
            $table->unsignedBigInteger('fk_servicio_id');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }
};