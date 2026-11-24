<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Models\Partida;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('name')->paginate(10);

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(UsuarioRequest $request)
    {
        User::create($request->validated());

        return redirect()->route('admin.usuarios.index')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(UsuarioRequest $request, User $usuario)
    {
        $datos = $request->validated();

        if (empty($datos['password'])) {
            unset($datos['password']);   // contraseña en blanco = no cambiarla
        }

        // Nadie cambia su propio rol
        if ($usuario->is($request->user())) {
            unset($datos['role']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    // Eliminación lógica: el historial de partidas conserva el nombre del usuario
    public function destroy(Request $request, User $usuario)
    {
        if ($usuario->is($request->user())) {
            return back()->withErrors(['usuario' => 'No puedes eliminar tu propia cuenta.']);
        }

        DB::transaction(function () use ($usuario) {
            // Sus partidas en curso o en espera se cancelan
            Partida::activas()->deUsuario($usuario->id)->update(['estado' => 'cancelada']);
            DB::table('sessions')->where('user_id', $usuario->id)->delete();
            $usuario->delete();
        });

        return redirect()->route('admin.usuarios.index')
            ->with('status', "{$usuario->name} fue eliminado.");
    }
}