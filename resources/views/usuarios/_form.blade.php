@php
    $editando = isset($usuario);
    $esPropio = $editando && $usuario->id === auth()->id();
@endphp

<div class="mb-3">
    <label class="form-label">Nombre</label>
    <input type="text" name="name" value="{{ old('name', $usuario->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Correo</label>
    <input type="email" name="email" value="{{ old('email', $usuario->email ?? '') }}"
           class="form-control @error('email') is-invalid @enderror">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Contraseña</label>
    <input type="password" name="password"
           class="form-control @error('password') is-invalid @enderror">
    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    @if ($editando) <div class="form-text">Déjala en blanco para conservar la actual.</div> @endif
</div>

<div class="mb-3">
    <label class="form-label">Confirmar contraseña</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>

@if (!$esPropio)
    <div class="mb-3">
        <label class="form-label">Rol</label>
        @php $rolActual = old('role', $usuario->role ?? 'jugador'); @endphp
        <select name="role" class="form-select @error('role') is-invalid @enderror">
            <option value="jugador" @selected($rolActual === 'jugador')>Jugador</option>
            <option value="admin" @selected($rolActual === 'admin')>Administrador</option>
        </select>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
@else
    <p class="text-muted small">No puedes cambiar tu propio rol.</p>
@endif