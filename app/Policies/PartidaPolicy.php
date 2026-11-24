<?php

namespace App\Policies;

use App\Models\Partida;
use App\Models\User;

class PartidaPolicy
{
    // Ver el tablero: un administrador o alguno de los dos jugadores
    public function view(User $user, Partida $partida): bool
    {
        return $user->esAdmin() || $partida->simboloDe($user) !== null;
    }

    // Jugar, mover o abandonar: solo un jugador que participa en esa partida
    public function play(User $user, Partida $partida): bool
    {
        return $user->esJugador() && $partida->simboloDe($user) !== null;
    }
}