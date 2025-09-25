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
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id('asignacion_id');
            $table->unsignedBigInteger('fk_usuario_id')->index();
            $table->unsignedBigInteger('fk_organizacion_id')->index();
            $table->unsignedBigInteger('fk_sucursal_id')->index();
            $table->unsignedBigInteger('fk_ventanilla_id')->nullable()->index();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fk_usuario_id')->references('usuario_id')->on('usuarios');
            $table->foreign('fk_organizacion_id')->references('organizacion_id')->on('organizaciones');
            $table->foreign('fk_sucursal_id')->references('sucursal_id')->on('sucursales');
            $table->foreign('fk_ventanilla_id')->references('ventanilla_id')->on('ventanillas');

            // Índice único compuesto para evitar asignaciones activas duplicadas
            $table->unique([
                'fk_usuario_id',
                'fk_organizacion_id',
                'fk_sucursal_id',
                'fk_ventanilla_id',
                'activo'
            ], 'asignacion_unica_activa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
