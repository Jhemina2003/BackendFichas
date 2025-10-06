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
        Schema::create('ventanilla_tipo_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ventanilla_id');
            $table->unsignedBigInteger('dominio_id'); // tipo_servicio
            $table->timestamps();

            $table->foreign('ventanilla_id')->references('ventanilla_id')->on('ventanillas')->onDelete('cascade');
            $table->foreign('dominio_id')->references('dominio_id')->on('dominios')->onDelete('cascade');
            $table->unique(['ventanilla_id', 'dominio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventanilla_tipo_servicio');
    }
};
