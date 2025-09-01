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
            $table->integer('fk_sucursal_id');
            $table->foreign('fk_sucursal_id')->references('sucursal_id')->on('sucursales');

            /**
             * Columnas
             */
            $table->integer('numero');
            $table->boolean('bloqueado')->default(false);
            $table->timestamps();
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
