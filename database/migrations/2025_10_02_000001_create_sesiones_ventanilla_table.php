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
        Schema::create('sesiones_ventanilla', function (Blueprint $table) {
            $table->id('sesion_ventanilla_id');
            $table->unsignedBigInteger('fk_sesion_id'); // Sesión global del día
            $table->unsignedBigInteger('fk_usuario_id'); // Usuario ventanilla
            $table->unsignedBigInteger('fk_ventanilla_id'); // Ventanilla
            $table->enum('estado', ['activa', 'cerrada']);
            $table->dateTime('hora_inicio');
            $table->dateTime('hora_cierre')->nullable();
            $table->timestamps();

            $table->foreign('fk_sesion_id')->references('sesion_id')->on('sesiones');
            $table->foreign('fk_usuario_id')->references('usuario_id')->on('usuarios');
            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones_ventanilla');
    }
};
