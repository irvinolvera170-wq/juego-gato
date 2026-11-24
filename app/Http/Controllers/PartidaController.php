<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PartidaController extends Controller
{
    public function index(Request $request)
    {
        return view('partidas.index', $this->datosLobby($request->user()));
    }

    // Solo el contenido del lobby: el navegador lo pide cada 4 segundos para refrescarse
    public function lobby(Request $request)
    {
        return view('partidas._lobby', $this->datosLobby($request->user()))
            ->header('Cache-Control', 'no-store');
    }

    public function historial(Request $request)
    {
        $partidas = Partida::deUsuario($request->user()->id)
            ->with(['jugadorX', 'jugadorO', 'ganador'])
            ->latest('id')
            ->paginate(10);

        return view('partidas.historial', compact('partidas'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        try {
            $partida = DB::transaction(function () use ($user) {
                // Bloquea la fila del propio usuario: serializa sus acciones (doble clic, dos pestañas)
                User::whereKey($user->id)->lockForUpdate()->first();

                if ($user->partidaActiva()) {
                    throw new \DomainException('Ya tienes una partida activa. Termínala o abandónala antes de crear otra.');
                }

                // El creador es X y mueve primero; el símbolo O se asigna a quien se una
                return Partida::create([
                    'jugador_x_id' => $user->id,
                    'turno' => 'X',
                    'tablero' => '---------',
                    'estado' => 'esperando',
                ]);
            });
        } catch (\DomainException $e) {
            return redirect()->route('partidas.index')->withErrors(['partida' => $e->getMessage()]);
        }

        return redirect()->route('partidas.show', $partida);
    }

    public function join(Request $request, Partida $partida)
    {
        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $partida) {
                User::whereKey($user->id)->lockForUpdate()->first();

                // Bloquea la partida: si dos jugadores intentan unirse a la vez, uno espera
                $p = Partida::whereKey($partida->id)->lockForUpdate()->firstOrFail();

                if ($p->estado !== 'esperando' || $p->jugador_o_id) {
                    throw new \DomainException('Esta partida ya no está disponible.');
                }
                if ($p->simboloDe($user) !== null) {
                    throw new \DomainException('No puedes unirte a tu propia partida.');
                }
                if ($user->partidaActiva()) {
                    throw new \DomainException('Ya tienes una partida activa. Termínala o abandónala antes de unirte a otra.');
                }

                $p->update(['jugador_o_id' => $user->id, 'estado' => 'en_curso']);
            });
        } catch (\DomainException $e) {
            return redirect()->route('partidas.index')->withErrors(['partida' => $e->getMessage()]);
        }

        return redirect()->route('partidas.show', $partida);
    }

    public function show(Request $request, Partida $partida)
    {
        Gate::authorize('view', $partida);   // 403 si no participa y no es administrador

        $partida->load(['jugadorX', 'jugadorO', 'ganador']);

        return view('partidas.show', [
            'partida' => $partida,
            'estado' => $partida->paraJson($request->user()),
        ]);
    }

    // Endpoint de polling: devuelve el estado actual de la partida en JSON
    public function estado(Request $request, Partida $partida)
    {
        Gate::authorize('view', $partida);

        return response()
            ->json($partida->paraJson($request->user()))
            ->header('Cache-Control', 'no-store');
    }

    public function abandon(Request $request, Partida $partida)
    {
        Gate::authorize('play', $partida);   // solo quien participa

        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $partida) {
                $p = Partida::whereKey($partida->id)->lockForUpdate()->firstOrFail();

                if (!$p->estaActiva()) {
                    throw new \DomainException('La partida ya terminó.');
                }

                // Gana el oponente (null si nadie se había unido todavía)
                $oponenteId = $p->simboloDe($user) === 'X' ? $p->jugador_o_id : $p->jugador_x_id;

                $p->update(['estado' => 'abandonada', 'ganador_id' => $oponenteId]);
            });
        } catch (\DomainException $e) {
            return redirect()->route('partidas.show', $partida)->withErrors(['partida' => $e->getMessage()]);
        }

        return redirect()->route('partidas.index')->with('status', 'Abandonaste la partida.');
    }

    private function datosLobby(User $user): array
    {
        // Jugadores que hoy están en una partida activa (no disponibles)
        $ocupados = Partida::activas()->get(['jugador_x_id', 'jugador_o_id'])
            ->flatMap(fn ($p) => [$p->jugador_x_id, $p->jugador_o_id])
            ->filter()
            ->unique()
            ->values();

        return [
            'partidaActiva' => $user->partidaActiva(),
            'disponibles' => Partida::where('estado', 'esperando')
                ->where('jugador_x_id', '!=', $user->id)
                ->with('jugadorX')
                ->latest('id')
                ->get(),
            'jugadores' => User::where('role', 'jugador')
                ->whereKeyNot($user->id)
                ->whereNotIn('id', $ocupados)
                ->orderBy('name')
                ->get(),
        ];
    }
}