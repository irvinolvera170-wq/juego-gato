@extends('layouts.app')

@section('title', 'Lobby')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Lobby</h3>

    <x-mensajes />

    <div id="lobby">
        @include('partidas._lobby')
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Refresca el lobby cada 4 segundos (polling)
    setInterval(async function () {
        try {
            const r = await fetch("{{ route('partidas.lobby') }}", {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (r.redirected) { location.reload(); return; }   // la sesión expiró
            if (r.ok) document.getElementById('lobby').innerHTML = await r.text();
        } catch (e) { /* sin conexión: se reintenta en el siguiente ciclo */ }
    }, 4000);
</script>
@endsection