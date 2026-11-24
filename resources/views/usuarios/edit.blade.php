@extends('layouts.app')

@section('title', 'Editar usuario')

@section('content')
<div class="container py-5 d-flex justify-content-center">
    <div class="card shadow-sm w-100" style="max-width: 440px;">
        <div class="card-body p-4">
            <h3 class="mb-4">Editar usuario</h3>

            <form method="POST" action="{{ route('admin.usuarios.update', $usuario) }}" novalidate>
                @csrf
                @method('PUT')
                @include('usuarios._form')
                <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
            </form>

            <a href="{{ route('admin.usuarios.index') }}" class="d-block text-center mt-3">← Volver a usuarios</a>
        </div>
    </div>
</div>
@endsection