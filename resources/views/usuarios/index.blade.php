@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Usuarios</h3>
                <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary btn-sm">+ Nuevo usuario</a>
            </div>

            <x-mensajes />

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Registro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $u)
                            <tr>
                                <td>{{ $u->name }} @if ($u->id === auth()->id()) <span class="text-muted">(tú)</span> @endif</td>
                                <td>{{ $u->email }}</td>
                                <td><span class="badge {{ $u->esAdmin() ? 'bg-danger' : 'bg-secondary' }}">{{ $u->role }}</span></td>
                                <td>{{ $u->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.usuarios.edit', $u) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        @if ($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}"
                                                  onsubmit="return confirm('¿Eliminar este usuario? Sus partidas activas se cancelarán.')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $usuarios->links() }}
        </div>
    </div>
</div>
@endsection