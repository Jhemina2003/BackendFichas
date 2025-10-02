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
            // Referencias
            $table->id('ficha_id');
            $table->integer('fk_sesion_id');
            $table->unsignedBigInteger('fk_servicio_id');
            $table->integer('fk_llamada_id')->nullable();
            $table->foreign('fk_sesion_id')->references('sesion_id')->on('sesiones');
            $table->foreign('fk_servicio_id')->references('servicio_id')->on('servicios');
            // La relación foránea fk_llamada_id se agregará en migración separada

            // Columnas
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

