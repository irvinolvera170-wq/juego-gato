<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jugador_x_id')->constrained('users');
            $table->foreignId('jugador_o_id')->nullable()->constrained('users');
            $table->char('turno', 1)->default('X');
            $table->char('tablero', 9)->default('---------');
            $table->string('estado')->default('esperando');
            $table->foreignId('ganador_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas');
    }
};