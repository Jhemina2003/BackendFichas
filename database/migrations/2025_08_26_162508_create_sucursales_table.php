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
            $table->integer('fk_organizacion_id');
            $table->foreign('fk_organizacion_id')->references('organizacion_id')->on('organizaciones');

            /**
             * Columnas
             */
            $table->string('nombre', 250);
            $table->timestamps();
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
