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
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('servicio_id');
            $table->unsignedBigInteger('fk_ventanilla_id');
            $table->unsignedBigInteger('fk_dominio_tipo_servicio_id');
            $table->date('fecha');
            $table->timestamps();

            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');
            $table->foreign('fk_dominio_tipo_servicio_id')->references('dominio_id')->on('dominios');
            $table->unique(['fk_ventanilla_id', 'fk_dominio_tipo_servicio_id', 'fecha'], 'servicio_ventanilla_fecha_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};
