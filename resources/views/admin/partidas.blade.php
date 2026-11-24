@extends('layouts.app')

@section('title', 'Todas las partidas')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="mb-3">Todas las partidas</h3>

            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ !$filtro ? 'active' : '' }}" href="{{ route('admin.partidas.index') }}">Todas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $filtro === 'activas' ? 'active' : '' }}"
                       href="{{ route('admin.partidas.index', ['estado' => 'activas']) }}">En curso o en espera</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $filtro === 'finalizadas' ? 'active' : '' }}"
                       href="{{ route('admin.partidas.index', ['estado' => 'finalizadas']) }}">Historial (finalizadas)</a>
                </li>
            </ul>

            <x-mensajes />

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jugador X</th>
                            <th>Jugador O</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partidas as $p)
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td>{{ $p->jugadorX->name }}</td>
                                <td>{{ $p->jugadorO?->name ?? '—' }}</td>
                                <td><x-estado-partida :partida="$p" :user="auth()->user()" /></td>
                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('partidas.show', $p) }}" class="btn btn-sm btn-outline-info">Ver</a>
                                        @if ($p->estaActiva())
                                            <form method="POST" action="{{ route('admin.partidas.cancel', $p) }}"
                                                  onsubmit="return confirm('¿Cancelar esta partida?')">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-warning">Cancelar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No hay partidas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $partidas->links() }}
        </div>
    </div>
</div>
@endsection