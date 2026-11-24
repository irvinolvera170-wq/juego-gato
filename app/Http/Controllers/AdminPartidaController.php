<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPartidaController extends Controller
{
    public function index(Request $request)
    {
        $filtro = $request->query('estado');

        $partidas = Partida::with(['jugadorX', 'jugadorO', 'ganador'])
            ->when($filtro === 'activas', fn ($q) => $q->activas())
            ->when($filtro === 'finalizadas', fn ($q) => $q->whereNotIn('estado', Partida::ACTIVAS))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.partidas', compact('partidas', 'filtro'));
    }

    public function cancel(Partida $partida)
    {
        try {
            DB::transaction(function () use ($partida) {
                $p = Partida::whereKey($partida->id)->lockForUpdate()->firstOrFail();

                if (!$p->estaActiva()) {
                    throw new \DomainException('Solo se pueden cancelar partidas en curso o en espera.');
                }

                $p->update(['estado' => 'cancelada']);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['partida' => $e->getMessage()]);
        }

        return back()->with('status', "La partida #{$partida->id} fue cancelada.");
    }
}