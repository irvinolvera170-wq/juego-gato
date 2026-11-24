@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
<div class="container py-5 d-flex justify-content-center">
    <div class="card shadow-sm w-100" style="max-width: 440px;">
        <div class="card-body p-4">
            <h3 class="mb-4">Crear usuario</h3>

            <form method="POST" action="{{ route('admin.usuarios.store') }}" novalidate>
                @csrf
                @include('usuarios._form')
                <button type="submit" class="btn btn-primary w-100">Crear usuario</button>
            </form>

            <a href="{{ route('admin.usuarios.index') }}" class="d-block text-center mt-3">← Volver a usuarios</a>
        </div>
    </div>
</div>
@endsection