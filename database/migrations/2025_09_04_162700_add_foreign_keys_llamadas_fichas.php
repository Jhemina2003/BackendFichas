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
        Schema::table('llamadas', function (Blueprint $table) {
            $table->foreign('fk_ficha_id')->references('ficha_id')->on('fichas');
        });
        Schema::table('fichas', function (Blueprint $table) {
            $table->foreign('fk_llamada_id')->references('llamada_id')->on('llamadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('llamadas', function (Blueprint $table) {
            $table->dropForeign(['fk_ficha_id']);
        });
        Schema::table('fichas', function (Blueprint $table) {
            $table->dropForeign(['fk_llamada_id']);
        });
    }
};
