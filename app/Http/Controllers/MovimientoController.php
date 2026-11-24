<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovimientoRequest;
use App\Models\Partida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MovimientoController extends Controller
{
    public function store(MovimientoRequest $request, Partida $partida)
    {
        Gate::authorize('play', $partida);   // 403 si no es jugador de esta partida

        $user = $request->user();
        $posicion = (int) $request->validated('posicion');

        try {
            $actual = DB::transaction(function () use ($partida, $user, $posicion) {
                // Bloquea la fila de la partida (SELECT ... FOR UPDATE).
                // Otra petición sobre la misma partida espera aquí hasta que esta termine.
                $p = Partida::whereKey($partida->id)->lockForUpdate()->firstOrFail();

                if ($p->estado !== 'en_curso') {
                    throw new \DomainException('La partida no está en curso.');
                }

                // El símbolo sale de la base de datos, nunca del navegador
                $simbolo = $p->simboloDe($user);

                if ($p->turno !== $simbolo) {
                    throw new \DomainException('No es tu turno.');
                }
                if ($p->tablero[$posicion] !== '-') {
                    throw new \DomainException('Esa casilla ya está ocupada.');
                }

                $tablero = substr_replace($p->tablero, $simbolo, $posicion, 1);

                $p->movimientos()->create([
                    'user_id' => $user->id,
                    'posicion' => $posicion,
                    'simbolo' => $simbolo,
                ]);

                $cambios = ['tablero' => $tablero];

                if (Partida::ganadorEn($tablero) !== null) {
                    $cambios['estado'] = 'ganada';
                    $cambios['ganador_id'] = $user->id;
                } elseif (!str_contains($tablero, '-')) {
                    $cambios['estado'] = 'empate';
                } else {
                    $cambios['turno'] = $simbolo === 'X' ? 'O' : 'X';
                }

                $p->update($cambios);

                return $p;
            });
        } catch (\DomainException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        }

        return response()->json($actual->paraJson($user));
    }
}