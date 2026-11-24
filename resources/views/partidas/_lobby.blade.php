@if ($partidaActiva)
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>
            Tienes una partida
            {{ $partidaActiva->estado === 'esperando' ? 'esperando oponente' : 'en curso' }}.
        </span>
        <a href="{{ route('partidas.show', $partidaActiva) }}" class="btn btn-sm btn-primary">Ir a mi partida</a>
    </div>
@else
    <form method="POST" action="{{ route('partidas.store') }}" class="mb-3">
        @csrf
        <button class="btn btn-success">+ Crear nueva partida</button>
    </form>
@endif

<div class="row g-3">
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5>Partidas disponibles</h5>

                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Creada por</th>
                            <th>Hace</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($disponibles as $d)
                            <tr>
                                <td>{{ $d->jugadorX->name }}</td>
                                <td>{{ $d->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    @if (!$partidaActiva)
                                        <form method="POST" action="{{ route('partidas.join', $d) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-primary">Unirse</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">No hay partidas esperando oponente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h5>Jugadores disponibles</h5>
                <p class="text-muted small">Jugadores que no están en una partida.</p>

                <ul class="list-group">
                    @forelse ($jugadores as $j)
                        <li class="list-group-item">{{ $j->name }}</li>
                    @empty
                        <li class="list-group-item text-muted">No hay jugadores disponibles.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>