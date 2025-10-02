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
        Schema::create('servicio_ventanilla', function (Blueprint $table) {
            $table->id('servicio_ventanilla_id');
            $table->unsignedBigInteger('fk_ventanilla_id');
            $table->unsignedBigInteger('fk_servicio_id');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');
            $table->foreign('fk_servicio_id')->references('servicio_id')->on('servicios');
            $table->unique(['fk_ventanilla_id', 'fk_servicio_id'], 'ventanilla_servicio_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio_ventanilla');
    }
};
