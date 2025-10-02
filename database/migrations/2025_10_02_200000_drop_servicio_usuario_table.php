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
        // Elimina la tabla servicio_usuario si existe
        Schema::dropIfExists('servicio_usuario');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se recrea la tabla
    }
};
