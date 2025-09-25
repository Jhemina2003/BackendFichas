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
    Schema::create('sucursales', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('sucursal_id');
            $table->integer('fk_organizacion_id')->index();
            $table->foreign('fk_organizacion_id')->references('organizacion_id')->on('organizaciones');

            /**
             * Columnas
             */
            $table->string('nombre', 250);
            $table->unique(['fk_organizacion_id', 'nombre'], 'sucursal_nombre_unico_por_org');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
