@extends('layouts.app')

@section('title', 'Mi historial')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="mb-3">Mi historial de partidas</h3>

            <x-mensajes />

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Oponente</th>
                            <th>Mi símbolo</th>
                            <th>Resultado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($partidas as $p)
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $p->oponenteDe(auth()->user())?->name ?? '—' }}</td>
                                <td>{{ $p->simboloDe(auth()->user()) }}</td>
                                <td><x-estado-partida :partida="$p" :user="auth()->user()" /></td>
                                <td class="text-end">
                                    <a href="{{ route('partidas.show', $p) }}" class="btn btn-sm btn-outline-info">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Todavía no has jugado ninguna partida.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $partidas->links() }}
        </div>
    </div>
</div>
@endsection