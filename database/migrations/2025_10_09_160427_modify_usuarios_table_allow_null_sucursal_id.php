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
        Schema::table('usuarios', function (Blueprint $table) {
            // Hacer que fk_sucursal_id pueda ser nullable
            $table->integer('fk_sucursal_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Revertir el cambio - hacer que fk_sucursal_id sea NOT NULL
            $table->integer('fk_sucursal_id')->nullable(false)->change();
        });
    }
};
