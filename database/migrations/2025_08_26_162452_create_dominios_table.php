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
        Schema::create('dominios', function (Blueprint $table) {
            /**
             * References
             */
            $table->id('dominio_id');
            $table->integer('fk_dominio_grupo_id');
            $table->foreign('fk_dominio_grupo_id')->references('dominio_grupo_id')->on('dominios_grupo');

            /**
             * Columnas
             */
            $table->string('nombre', 250);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dominios');
    }
};
