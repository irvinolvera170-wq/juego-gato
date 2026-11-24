@extends('layouts.app')

@section('title', 'Partida #'.$partida->id)

@section('content')
<style>
    .tablero { display: grid; grid-template-columns: repeat(3, 96px); gap: 8px; justify-content: center; }
    .celda { width: 96px; height: 96px; font-size: 2.6rem; font-weight: 700; padding: 0; }
    .celda:disabled { opacity: 1; }
    .celda:not(:disabled):hover { background: #e7f1ff; }
    .ganadora { background: #d1e7dd !important; border-width: 3px; }
</style>

<div class="container py-4" style="max-width: 520px;">
    <div class="card shadow-sm">
        <div class="card-body text-center">

            <h4 class="mb-3">Partida #{{ $partida->id }}</h4>

            <div class="d-flex justify-content-around mb-3">
                <div><span class="badge bg-primary">X</span> <span id="jugador-x">{{ $partida->jugadorX->name }}</span></div>
                <div><span class="badge bg-danger">O</span> <span id="jugador-o">{{ $partida->jugadorO?->name ?? 'Esperando...' }}</span></div>
            </div>

            <div id="mensaje" class="alert alert-info"></div>
            <div id="aviso" class="alert alert-danger d-none"></div>

            <div class="tablero my-3">
                @for ($i = 0; $i < 9; $i++)
                    <button type="button" class="btn btn-outline-secondary celda" data-pos="{{ $i }}" disabled></button>
                @endfor
            </div>

            <div class="d-flex justify-content-center gap-2">
                <a href="{{ auth()->user()->esAdmin() ? route('admin.partidas.index') : route('partidas.index') }}"
                   class="btn btn-outline-secondary">Volver</a>

                @if (auth()->user()->esJugador() && $partida->simboloDe(auth()->user()) && $partida->estaActiva())
                    <form id="form-abandonar" method="POST" action="{{ route('partidas.abandon', $partida) }}"
                          onsubmit="return confirm('¿Abandonar la partida? Tu oponente ganará.')">
                        @csrf
                        <button class="btn btn-warning">Abandonar partida</button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const estadoInicial = @json($estado);
    const urlEstado = "{{ route('partidas.estado', $partida) }}";
    const urlMover = "{{ route('partidas.mover', $partida) }}";
    const csrf = document.querySelector('meta[name=csrf-token]').content;

    let intervalo = null;
    let enviando = false;

    function pintar(e) {
        const caja = document.getElementById('mensaje');
        caja.className = 'alert alert-' + e.color;
        caja.textContent = e.mensaje;

        document.getElementById('jugador-x').textContent = e.jugadores.X;
        document.getElementById('jugador-o').textContent = e.jugadores.O ?? 'Esperando...';

        document.querySelectorAll('.celda').forEach(function (celda) {
            const i = Number(celda.dataset.pos);
            const valor = e.tablero[i];

            celda.textContent = valor === '-' ? '' : valor;
            celda.disabled = !(e.es_mi_turno && valor === '-');

            let clase = 'btn celda ';
            if (valor === 'X') clase += 'btn-outline-primary';
            else if (valor === 'O') clase += 'btn-outline-danger';
            else clase += 'btn-outline-secondary';

            if (e.linea_ganadora && e.linea_ganadora.includes(i)) clase += ' ganadora';
            celda.className = clase;
        });

        if (e.terminada) {
            if (intervalo) clearInterval(intervalo);
            const f = document.getElementById('form-abandonar');
            if (f) f.classList.add('d-none');
        }
    }

    function avisar(texto) {
        const aviso = document.getElementById('aviso');
        aviso.textContent = texto;
        aviso.classList.remove('d-none');
        setTimeout(function () { aviso.classList.add('d-none'); }, 3000);
    }

    // Consulta periódica del estado (polling)
    async function consultar() {
        try {
            const r = await fetch(urlEstado, { headers: { 'Accept': 'application/json' } });
            if (r.redirected) { location.reload(); return; }   // la sesión expiró
            if (r.ok) pintar(await r.json());
        } catch (e) { /* sin conexión: se reintenta en el siguiente ciclo */ }
    }

    async function mover(posicion) {
        if (enviando) return;
        enviando = true;

        try {
            const r = await fetch(urlMover, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ posicion: posicion })
            });
            const datos = await r.json();

            if (r.ok) {
                pintar(datos);
            } else {
                avisar(datos.mensaje || datos.message || 'No se pudo realizar el movimiento.');
                await consultar();
            }
        } catch (e) {
            avisar('No se pudo conectar con el servidor.');
        } finally {
            enviando = false;
        }
    }

    document.querySelectorAll('.celda').forEach(function (celda) {
        celda.addEventListener('click', function () { mover(Number(celda.dataset.pos)); });
    });

    pintar(estadoInicial);
    if (!estadoInicial.terminada) {
        intervalo = setInterval(consultar, 2000);
    }
</script>
@endsection