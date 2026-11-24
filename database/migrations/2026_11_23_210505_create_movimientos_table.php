<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partida_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->unsignedTinyInteger('posicion');
            $table->char('simbolo', 1);
            $table->timestamps();

            // Garantía extra en la base: una casilla solo se puede ocupar una vez por partida
            $table->unique(['partida_id', 'posicion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};