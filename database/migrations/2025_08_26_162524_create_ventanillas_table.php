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
    Schema::create('ventanillas', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('ventanilla_id');
            $table->integer('fk_sucursal_id')->index();
            $table->foreign('fk_sucursal_id')->references('sucursal_id')->on('sucursales');

            /**
             * Columnas
             */
            $table->integer('numero');
            $table->unique(['fk_sucursal_id', 'numero'], 'ventanilla_numero_unico_por_sucursal');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventanillas');
    }
};
